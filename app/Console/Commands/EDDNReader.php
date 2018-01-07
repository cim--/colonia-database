<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Station;
use App\Models\Faction;
use App\Models\State;
use App\Models\Influence;
use App\Models\Facility;
use App\Models\Alert;
use App\Models\Commodity;
use App\Models\Reserve;
use App\Models\Module;

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
        if (!isset($event['message']['timestamp'])) {
            return;
        }
        $generated = new Carbon($event['message']['timestamp']);
        if ($generated->addHour()->isPast()) {
            // ignore data more than 1 hour old
            return;
        }
        if ($event['$schemaRef'] == "http://schemas.elite-markets.net/eddn/journal/1" || $event['$schemaRef'] == "https://eddn.edcd.io/schemas/journal/1") {
            if ($event['message']['event'] == "FSDJump") {
                if ($event['message']['StarPos'][2] < 10000) {
                    // in case of duplicate names
                    return;
                }
                $this->processFSDJump($event);
            } else if ($event['message']['event'] == "Docked") {
                $this->processStationDocking($event);
            }
        } else if ($event['$schemaRef'] == "https://eddn.edcd.io/schemas/commodity/3") {
            $this->processCommodityReserveEvent($event);
        } else if ($event['$schemaRef'] == "https://eddn.edcd.io/schemas/outfitting/2") {
            $this->processOutfittingEvent($event);
        }
    }

    private function processFSDJump($event) {
        $system = System::where('name', $event['message']['StarSystem'])
            ->orWhere('catalogue', $event['message']['StarSystem'])
            ->first();
        if ($system && $system->population > 0 && isset($event['message']['Factions'])) {

            \Log::info("Incoming data", [
                'system' => $system->displayName()
            ]);
                    
            $this->line("[".date("YmdHis")."] FSDJump event for ".$system->displayName());
            $factions = $event['message']['Factions'];
            $influences = [];
            foreach ($factions as $faction) {
                if ($faction['Name'] == "Pilots Federation Local Branch") {
                    // virtual faction, ignore
                    continue;
                }
                $fo = Faction::where('name', $faction['Name'])->first();
                if (!$fo) {
                    $error = "Unrecognised faction ".$faction['Name']." in ".$system->displayName();
                    Alert::alert($error);
                    \Log::error($error);
                    $this->error($error);
                    return;
                }
                $inf = round($faction['Influence'], 3)*100;
                $faction['FactionState'] = $this->renameState($faction['FactionState']);
                $state = State::where('name', $faction['FactionState'])->first();
                if (!$state) {
                    $error = "Unrecognised faction state ".$faction['FactionState']." for ".$faction['Name']." in ".$system->displayName();
                    Alert::alert($error);
                    \Log::error($error);
                    $this->error($error);
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

            $this->updateSecurity($system, $event['message']);
        } else if ($event['message']['Population'] > 0 && $event['message']['StarPos'][2] > 18000) {
            $traditional = new \stdClass;
            $traditional->x = $event['message']['StarPos'][0];
            $traditional->y = $event['message']['StarPos'][1];
            $traditional->z = $event['message']['StarPos'][2];
            
            $coords = \App\Util::coloniaCoordinates($traditional);
            $colonia = new \stdClass;
            $colonia->x = 0;
            $colonia->y = 0;
            $colonia->z = 0;
            if (\App\Util::distance($coords, $colonia) < 1000) {
                Alert::alert("New inhabited system ".$event['message']['StarSystem']);
            }
        }
    }

    private function renameState($state) {
        if ($state == "CivilUnrest") {
            // to our name
            return "Civil Unrest";
        }
        if ($state == "CivilWar") {
            // no real need to distinguish
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
                Alert::alert("Unrecognised pending state $statename");
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

    private function updateSecurity($system, $message) {
        if (isset($message['Population'])) {
            $population = $message['Population'];
            if ($population > 0) {
                if ($population != $system->population) {
                    Alert::alert($system->displayName()." population change reported from ".$system->population." to ".$population);
// seeing some odd events here
//                    $system->population = $population;
//                    $system->save();
                } 
            } else {
                $this->error("Population 0 reported by Journal");
            }
        }

        if (isset($message['SystemSecurity'])) {
            switch ($message['SystemSecurity']) {
            case '$SYSTEM_SECURITY_high;':
                $system->security = "High";
                $this->removeBroker($system);
                break;
            case '$SYSTEM_SECURITY_medium;':
                $system->security = "Medium";
                $this->removeBroker($system);
                break;
            case '$SYSTEM_SECURITY_low;':
                $system->security = "Low";
                $this->addBroker($system);
                break;
            default:
                $this->error("Unrecognised security level ".$message['SystemSecurity']);
            }
            $system->save();
        }
    }

    private function removeBroker($system) {
        $broker = Facility::where('name', 'Broker')->first();

        foreach ($system->stations as $station) {
            $station->facilities()->detach($broker->id);
        }
    }

    private function addBroker($system) {
        $broker = Facility::where('name', 'Broker')->first();

        foreach ($system->stations as $station) {
            /* detach than reattach in case it's already there */
            $station->facilities()->detach($broker->id);
            $station->facilities()->attach($broker->id);
        }
    }

    private function processStationDocking($event) {
        $system = System::where('name', $event['message']['StarSystem'])
            ->orWhere('catalogue', $event['message']['StarSystem'])
            ->first();
        if (!$system) {
            return;
        }
        
        $station = Station::where('name', $event['message']['StationName'])
            ->where('system_id', $system->id)->first();
        if (!$station) {
            Alert::alert("Unknown station ".$event['message']['StationName']." in ".$system->displayName());
            return;
        }
        $this->line("[".date("YmdHis")."] Docking event for ".$system->displayName().": ".$station->name);

        if (strtolower($station->faction->name) != strtolower($event['message']['StationFaction'])) {
            Alert::alert("Ownership changed ".$station->name." was '".$station->faction->name."' is now '".$event['message']['StationFaction']."'");
            // for now, don't automatically update
        }
    }

    private function processCommodityReserveEvent($event) {
        $system = System::where('name', $event['message']['systemName'])
            ->orWhere('catalogue', $event['message']['systemName'])
            ->first();
        if (!$system) {
            return;
        }
        
        $station = Station::where('name', $event['message']['stationName'])
            ->where('system_id', $system->id)->first();
        if (!$station) {
            // no alert - the Docking event should already have done it
            return;
        }
        $this->line("[".date("YmdHis")."] Commodity event for ".$system->displayName().": ".$station->name);

        $state = $station->faction->currentState($station->system);
        if ($state->name == "None") {
            $states = $station->faction->currentStates();
            foreach ($states as $otherstate) {
                if ($otherstate->name == "War" || $otherstate->name == "Election") {
                    // treat conflict elsewhere as active here
                    $state = $otherstate;
                }
            }
        }
        
        Reserve::where('station_id', $station->id)->update(['current' => false]);

        foreach ($event['message']['commodities'] as $cdata) {
            $commodity = Commodity::firstOrCreate(['name' => $cdata['name']]);

            $reserve = new Reserve;
            $reserve->current = true;
            $reserve->date = new Carbon($event['message']['timestamp']);
            $reserve->commodity_id = $commodity->id;
            $reserve->station_id = $station->id;
            $reserve->state_id = $state->id;

            if ($cdata['stock'] > 0) {
                $reserve->reserves = $cdata['stock'];
                $reserve->price = $cdata['buyPrice'];
            } else {
                $reserve->reserves = -$cdata['demand'];
                $reserve->price = $cdata['sellPrice'];
            }
            $reserve->save();
        }
    }

    private function processOutfittingEvent($event) {
        $system = System::where('name', $event['message']['systemName'])
            ->orWhere('catalogue', $event['message']['systemName'])
            ->first();
        if (!$system) {
            return;
        }
        
        $station = Station::where('name', $event['message']['stationName'])
            ->where('system_id', $system->id)->first();
        if (!$station) {
            // no alert - the Docking event should already have done it
            return;
        }
        $this->line("[".date("YmdHis")."] Outfitting event for ".$system->displayName().": ".$station->name);

        $modules = [];
        foreach ($event['message']['modules'] as $modulecode) {
            $module = Module::where('eddn', $modulecode)->withCount('stations')->first();
            if (!$module) {
                // ignore for now
            } else {
                $modules[] = $module->id;
                if ($module->stations_count == 0) {
                    // not seen before
                    Alert::alert("Module ".$module->displayName()." now available at ".$station->name);
                }
            }
        }
        /* Use syncWithoutDetaching to avoid people without
         * horizons/Cobra IV/etc. making the availability disappear
         * when it's just that they personally can't see it */
        $station->modules()->syncWithoutDetaching($modules);
    }
    
}
