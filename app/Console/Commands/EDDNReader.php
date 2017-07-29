<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Faction;
use App\Models\State;
use App\Models\Influence;

class EDDNReader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:eddnreader {--monitor}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read live data from EDDN';

    private $relay = 'tcp://eddn.edcd.io:9500';

    private $monitoronly = false;
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->option('monitor')) {
            $this->monitoronly = true;
        }
        $context    = new \ZMQContext();
        $subscriber = $context->getSocket(\ZMQ::SOCKET_SUB);
        $subscriber->setSockOpt(\ZMQ::SOCKOPT_SUBSCRIBE, "");
        $subscriber->setSockOpt(\ZMQ::SOCKOPT_RCVTIMEO, 600000);

        while (true) {
            try {
                
                $subscriber->connect($this->relay);
                $this->info("EDDN Connection Online");
                while (true) {
                    $message = $subscriber->recv();
                    
                    if ($message === false) {
                        $this->error("Connection timeout on socket - reconnecting");
                        $subscriber->disconnect($this->relay);
                        break;
                    }
                    
                    $message    = zlib_decode($message);
                    $json       = $message;
                    
                    $this->process(json_decode($json, true));
                }
            } catch (\ZMQSocketException $e) {
                $this->error('ZMQSocketException: ' . $e);
                sleep(10);
            }
        }        
    }

    private function process($event)
    {
        if ($event['$schemaRef'] == "http://schemas.elite-markets.net/eddn/journal/1" || $event['$schemaRef'] == "https://eddn.edcd.io/schemas/journal/1") {
            if ($event['message']['event'] == "FSDJump") {
                if ($event['message']['StarPos'][2] < 10000) {
                    // don't process Ogma and Ratri in the Sol bubble
                    return;
                }

                $system = System::where('name', $event['message']['StarSystem'])
                    ->orWhere('catalogue', $event['message']['StarSystem'])
                    ->first();
                if ($system && $system->population > 0 && isset($event['message']['Factions'])) {
                    $this->line("[".date("YmdHis")."] Incoming event for ".$system->displayName());
                    $factions = $event['message']['Factions'];
                    $influences = [];
                    foreach ($factions as $faction) {
                        $fo = Faction::where('name', $faction['Name'])->first();
                        if (!$fo) {
                            \Log::error("Unrecognised faction ".$faction['Name']." in ".$system->displayName());
                            $this->error("Unrecognised faction ".$faction['Name']." in ".$system->displayName());
                            return;
                        }
                        $inf = round($faction['Influence'], 3)*100;
                        $faction['FactionState'] = $this->renameState($faction['FactionState']);
                        $state = State::where('name', $faction['FactionState'])->first();
                        if (!$state) {
                            \Log::error("Unrecognised faction state ".$faction['FactionState']." for ".$faction['Name']." in ".$system->displayName());
                            $this->error("Unrecognised faction state ".$faction['FactionState']." for ".$faction['Name']." in ".$system->displayName());
                            return;
                        }
                        $pending = [];
                        if (isset($faction['PendingStates'])) {
                            $pending = $faction['PendingStates'];
                        }
                        $influences[] = ['faction' => $fo, 'influence' => $inf, 'state' => $state, 'pending' => $pending];
                    }
                    usort($influences, function($a, $b) {
                        return $b['influence'] - $a['influence'];
                    });
                    $this->updateInfluences($system, $influences);
                }
            }
        }
    }

    private function renameState($state) {
        if ($state == "CivilUnrest") {
            // to our name
            return "Civil Unrest";
        }
        if ($state == "CivilWar") {
            return "War";
        }
        return $state;
    }

    private function updateInfluences($system, $influences) {
        $target = \App\Util::tick();

        $exists = Influence::where('system_id', $system->id)
            ->where('date', $target->format("Y-m-d 00:00:00"))
            ->count();
        if ($exists > 0) {
            // already have data for this tick
            // but it might have been manually entered so
            // there might be pending states still to get
            \DB::transaction(function() use ($influences) {
                foreach ($influences as $influence) {
                    $this->updatePendingStates($influence['faction'], $influence['pending']);
                }
                $this->info("Updated pending states");
            });
            return;
        }

        $latest = Influence::where('system_id', $system->id)
            ->where('faction_id', $influences[0]['faction']->id)
            ->where('current', 1)
            ->first();
        if ($latest) {
            // if not, then new system being read
            if(abs($latest->influence - $influences[0]['influence']) < 0.1) {
                // data is too close to existing data, may be stale
                // usort() in process() above ensures we're looking at
                // the largest one which is most likely to change anyway
                if (\App\Util::fairlyNearTick()) {
                    $this->error("Data looks stale - skipping");
                    return;
                } else {
                    $this->info("Data unchanged - processing after 4 hours");
                }
            }
        }

        if ($this->monitoronly) {
            $this->info("Monitor only: no updated influence for ".$system->displayName());
            return;
        }

        
        \DB::transaction(function() use ($system, $influences, $target) {
            Influence::where('system_id', $system->id)
                ->where('current', true)
                ->update(['current' => false]);

            foreach ($influences as $influence) {
                $io = new Influence;
                $io->system_id = $system->id;
                $io->faction_id = $influence['faction']->id;
                $io->state_id = $influence['state']->id;
                $io->influence = $influence['influence'];
                $io->current = 1;
                $io->date = $target;
                $io->save();
            }
            \Log::info("Influence update", [
                'system' => $system->displayName(),
                'user' => "EDDN Feed"
            ]);
            $this->info("Updated influence for ".$system->displayName());

            foreach ($influences as $influence) {
                $this->updatePendingStates($influence['faction'], $influence['pending']);
            }
            $this->info("Updated pending states");
                            
        });
    }

    private function updatePendingStates($faction, $pending) {
        $states = [];
        $haswar = false;
        $haselection = false;
        foreach ($pending as $entry) {
            $statename = $this->renameState($entry['State']);
            $state = State::where('name', $statename)->first();
            if (!$state) {
                $this->error("Unrecognised pending state $statename");
                return;
            }
            if ($statename == "War") {
                $haswar = true;
            }
            if ($statename == "Election") {
                $haselection = true;
            }
            $states[] = $state;
        }
        if (!$haswar) {
            $war = $faction->states()->where('name', 'War')->first();
            if ($war) {
                // war is currently in the pending states but not here
                // might still be pending elsewhere unless it's current elsewhere
                $currentwar = $faction->influences()->where('current', 1)
                                      ->where('state_id', $war->id)->first();
                if (!$currentwar) {
                    // war pending but not active, add back to pending states
                    $states[] = $war;
                }
            }
        }
        if (!$haselection) {
            $election = $faction->states()->where('name', 'Election')->first();
            if ($election) {
                // election is currently in the pending states but not here
                // might still be pending elsewhere unless it's current elsewhere
                $currentelection = $faction->influences()->where('current', 1)
                                           ->where('state_id', $election->id)->first();
                if (!$currentelection) {
                    // election pending but not active, add back to pending states
                    $states[] = $election;
                }
            }
        }

        if (count($states) == 0) {
            // no pending states
            $states[] = State::where('name', 'None')->first();
        }

        $tick = \App\Util::tick();
        $sync = [];
        foreach ($states as $state) {
            $sync[$state->id] = ['date' => $tick->format('Y-m-d 00:00:00')];
        }

        if ($this->monitoronly) {
//            $this->info("Monitor only: no updated pending states for ".$faction->name);
            return;
        }

        
        $faction->states()->sync($sync);
        
//        $this->info("Updated pending states for ".$faction->name);
    }
}
