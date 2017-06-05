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
    protected $signature = 'cdb:eddnreader';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Read live data from EDDN';

    private $relay = 'tcp://eddn-relay.elite-markets.net:9500';
    
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
        if ($event['$schemaRef'] == "http://schemas.elite-markets.net/eddn/journal/1") {
            if ($event['message']['event'] == "FSDJump") {

                $system = System::where('name', $event['message']['StarSystem'])
                    ->orWhere('catalogue', $event['message']['StarSystem'])
                    ->first();
                if ($system && $system->population > 0 && isset($event['message']['Factions'])) {
                    $this->line("Incoming event for ".$system->displayName());
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
                        if ($faction['FactionState'] == "CivilUnrest") {
                            // to our name
                            $faction['FactionState'] == "Civil Unrest";
                        }
                        $state = State::where('name', $faction['FactionState'])->first();
                        if (!$state) {
                            \Log::error("Unrecognised faction state ".$faction['FactionState']." for ".$faction['Name']." in ".$system->displayName());
                            $this->error("Unrecognised faction state ".$faction['FactionState']." for ".$faction['Name']." in ".$system->displayName());
                            return;
                        }
                        $influences[] = ['faction' => $fo, 'influence' => $inf, 'state' => $state];
                    }
                    usort($influences, function($a, $b) {
                        return $b['influence'] - $a['influence'];
                    });
                    $this->updateInfluences($system, $influences);
                }
            }
        }
    }

    private function updateInfluences($system, $influences) {
        $target = \App\Util::tick();

        $exists = Influence::where('system_id', $system->id)
            ->where('date', $target->format("Y-m-d 00:00:00"))
            ->count();
        if ($exists > 0) {
            return; // already have data for this tick
        }

        if (\App\Util::nearTick()) {
            $latest = Influence::where('system_id', $system->id)
                ->where('faction_id', $influences[0]['faction']->id)
                ->where('current', 1)
                ->first();
            if(abs($latest->influence - $influences[0]['influence']) <= 0.2) {
                // data is too close to existing data, may be stale
                // usort() in process() above ensures we're looking at
                // the largest one which is most likely to change anyway
                $this->error("Data looks stale - skipping");
                return; 
            }
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
        });
    }
}
