<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Station;
use App\Models\Stationclass;
use App\Models\Faction;
use App\Models\State;
use App\Models\Influence;
use App\Models\Facility;
use App\Models\Alert;
use App\Models\Commodity;
use App\Models\Reserve;
use App\Models\Module;
use App\Models\Eddnevent;
use App\Models\Eddnblacklist;
use App\Models\Ship;
use App\Models\Installation;
use App\Models\Installationclass;
use App\Models\Conflict;

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
        if (Eddnblacklist::check($event['header']['uploaderID'])) {
            // this uploaderID is on a server sending unreliable information
            return;
        }
        if (!isset($event['header']['gameversion']) ||
            $event['header']['gameversion'] == '' ||
            substr($event['header']['gameversion'], 0, 11) == "CAPI-legacy" ||
            substr($event['header']['gameversion'], 0, 1) == "3" 
        ) {
            // ignore legacy entry
            // and ignore entries with no version header
            //$this->error("Ignoring no gameversion"); // TEST
            return;
        }
        
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
                if ($event['message']['StarPos'][2] > 6000) {
                    // in case of duplicate names
                    return;
                }
                $this->processFSDJump($event);
            } else if ($event['message']['event'] == "Location" || $event['message']['event'] == "CarrierJump") {
                if ($event['message']['StarPos'][2] > 6000) {
                    // in case of duplicate names
                    return;
                }
                $this->processLocation($event);
            } else if ($event['message']['event'] == "Docked") {
                $this->processStationDocking($event);
            }
        } else if ($event['$schemaRef'] == "https://eddn.edcd.io/schemas/commodity/3") {
            $this->processCommodityReserveEvent($event);
        } else if ($event['$schemaRef'] == "https://eddn.edcd.io/schemas/outfitting/2") {
            $this->processOutfittingEvent($event);
        } else if ($event['$schemaRef'] == "https://eddn.edcd.io/schemas/shipyard/2") {
            $this->processShipyardEvent($event);
        }
    }

    private function unreliableFSDEvent($event) {
        switch ($event['message']['StarSystem']) {
        case "Colonia":
            $badpop = 750;
            break;
        case "Ogmar":
            $badpop = 4500;
            break;
        case "Ratraii":
            $badpop = 25000;
            break;
        case "Hephaestus":
            $badpop = 3200;
            break;
        case "Trakath":
            $badpop = 1900;
            break;
        case "Tenjin":
            $badpop = 1500;
            break;
        case "Pennsylvania":
            $badpop = 2000;
            break;
            //        case "Desy": // uncomment when it changes
            //                        $badpop = 20000;
            //                        break;
        case "Metztli":
            $badpop = 15000;
            break;
        case "HIP 23759":
            $badpop = 2500;
            break;
        default:
            return false; // none known for this system
        }
        if ($event['message']['Population'] == $badpop) {
            \Log::info("Blacklisted data", [
                'system' => $event['message']['StarSystem'],
                'population' => $event['message']['Population'],
            ]);
            return true;
        }
        return false;
    }

    private function processFSDJump($event) {
        $system = System::where('name', $event['message']['StarSystem'])
            ->orWhere('catalogue', $event['message']['StarSystem'])
            ->first();
        if ($system && $system->population > 0 && isset($event['message']['Factions'])) {

            if ($this->unreliableFSDEvent($event)) {
                Eddnblacklist::blacklist($event['header']['uploaderID']);
                return;
            }
            
            \Log::info("Incoming data", [
                'system' => $system->displayName()
            ]);
            $eddnevent = new Eddnevent;
            $eddnevent->system_id = $system->id;
            $eddnevent->eventtime = Carbon::now();
            $eddnevent->save();
                    
            $this->line("[".date("YmdHis")."] FSDJump event for ".$system->displayName());

            $this->processSystemData($event, $system);
            
        } else if ($event['message']['Population'] > 0 && $event['message']['StarPos'][2] < -600) {
            $traditional = new \stdClass;
            $traditional->x = $event['message']['StarPos'][0];
            $traditional->y = $event['message']['StarPos'][1];
            $traditional->z = $event['message']['StarPos'][2];
            
            $coords = \App\Util::coloniaCoordinates($traditional);
            $colonia = new \stdClass;
            $colonia->x = 0;
            $colonia->y = 0;
            $colonia->z = 0;
            if (\App\Util::distance($coords, $colonia) < 250) {
                Alert::alert("New inhabited system ".$event['message']['StarSystem']);
            }
        }
    }

    private function processLocation($event) {
        $system = System::where('name', $event['message']['StarSystem'])
            ->orWhere('catalogue', $event['message']['StarSystem'])
            ->first();
        if ($system && $system->population > 0 && isset($event['message']['Factions'])) {

            if ($this->unreliableFSDEvent($event)) {
                Eddnblacklist::blacklist($event['header']['uploaderID']);
                return;
            }
            
            \Log::info("Incoming data", [
                'system' => $system->displayName()
            ]);
            // no eddn event count for Location or CarrierJump as that
            // doesn't imply a traffic report entry
            
            $this->line("[".date("YmdHis")."] Location event for ".$system->displayName());

            $this->processSystemData($event, $system);
            
        } else if ($event['message']['Population'] > 0 && $event['message']['StarPos'][2] < -600) {
            $traditional = new \stdClass;
            $traditional->x = $event['message']['StarPos'][0];
            $traditional->y = $event['message']['StarPos'][1];
            $traditional->z = $event['message']['StarPos'][2];
            
            $coords = \App\Util::coloniaCoordinates($traditional);
            $colonia = new \stdClass;
            $colonia->x = 0;
            $colonia->y = 0;
            $colonia->z = 0;
            if (\App\Util::distance($coords, $colonia) < 250) {
                Alert::alert("New inhabited system ".$event['message']['StarSystem']);
            }
        }
    }

    
    private function processSystemData($event, $system) {
        if (!$system->virtualonly) {
            $isconflicts = false;
            
            $factions = $event['message']['Factions'];
            $influences = [];
            foreach ($factions as $faction) {
                if ($faction['Name'] == "Pilots' Federation Local Branch") {
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
                if ($fo->virtual) {
                    // virtual faction, ignore
                    continue;
                }
                $inf = round($faction['Influence'], 3)*100;
                $hap = substr($faction['Happiness'],22,1);
                $states = isset($faction['ActiveStates']) ? $faction['ActiveStates'] : [];
                $active = [];
                foreach ($states as $fstate) {
                    $fstate = $this->renameState($fstate['State']);
                    if ($fstate == "War" || $fstate == "Election") {
                        $isconflicts = true;
                    }
                    $state = State::where('name', $fstate)->first();
                    if (!$state) {
                        $error = "Unrecognised faction state ".$fstate." for ".$faction['Name']." in ".$system->displayName();
                        Alert::alert($error);
                        \Log::error($error);
                        $this->error($error);
                        return;
                    }
                    $active[] = $state;
                }

                /* Detect if there should be a Conflicts array */
                $pstates = isset($faction['PendingStates']) ? $faction['PendingStates'] : [];
                foreach ($pstates as $pstate) {
                    $fstate = $this->renameState($pstate['State']);
                    if ($fstate == "War" || $fstate == "Election") {
                        $isconflicts = true;
                    }
                }
                $rstates = isset($faction['RecoveringStates']) ? $faction['RecoveringStates'] : [];
                foreach ($rstates as $rstate) {
                    $fstate = $this->renameState($rstate['State']);
                    if ($fstate == "War" || $fstate == "Election") {
                        $isconflicts = true;
                    }
                }
                

                if (!in_array($hap, [1,2,3,4,5])) {
                    $error = "Happiness value ".$faction['Happiness']." unrecognised for ".$faction['Name']." in ".$system->displayName();
                    \Log::error($error);
                    $this->error($error);
                    // it seems to just be the happiness data, so set
                    // default and use the rest
                    $hap = 2;
                }
		/*
                if ($faction['FactionState'] != "None") {
                    $fstate = $this->renameState($faction['FactionState']);
                    $state = State::where('name', $fstate)->first();
                    if (!$state) {
                        $error = "Unrecognised faction state ".$fstate." for ".$faction['Name']." in ".$system->displayName();
                        Alert::alert($error);
                        \Log::error($error);
                        $this->error($error);
                        return;
                    }
                    $active[] = $state;
		}
		 */
                if (count($active) == 0) {
                    $active[] = State::where('name', 'None')->first();
                }
                $pending = [];
                if (isset($faction['PendingStates'])) {
                    $pending = $faction['PendingStates'];
                }
                $influences[] = ['faction' => $fo, 'influence' => $inf, 'state' => collect($active), 'pending' => $pending, 'happiness' => $hap];
            }
            usort($influences, function($a, $b) {
                return $b['influence'] - $a['influence'];
            });
            $this->updateInfluences($system, $influences);

            if (isset($event['message']['Conflicts'])) {
                $this->updateConflicts($system, $event['message']['Conflicts']);
            } else if (!$isconflicts) {
                // the field is missing because there aren't any
                // sometimes it's missing because older software
                // doesn't send it to EDDN
                $this->updateConflicts($system, []);
            }
        }
        $this->updateSecurity($system, $event['message']);

    }

    private function renameState($state) {
        if ($state == "CivilLiberty") {
            // to our name
            return "Civil Liberty";
        }
        if ($state == "InfrastructureFailure") {
            // to our name
            return "Infrastructure Failure";
        }
        if ($state == "NaturalDisaster") {
            // to our name
            return "Natural Disaster";
        }
        if ($state == "PublicHoliday") {
            // to our name
            return "Public Holiday";
        }
        if ($state == "CivilUnrest") {
            // to our name
            return "Civil Unrest";
        }
        if ($state == "CivilWar") {
            // no real need to distinguish
            return "War";
        }
        if ($state == "PirateAttack") {
            // to our name
            return "Pirate Attack";
        }
        return $state;
    }

    private function updateInfluences($system, $influences) {
        $target = \App\Util::tick();

        $latest = Influence::where('system_id', $system->id)
            ->where('faction_id', $influences[0]['faction']->id)
            ->where('current', 1)
            ->first();
        $exists = Influence::where('system_id', $system->id)
            ->where('date', $target->format("Y-m-d 00:00:00"))
            ->count();
        $overwrite = false;

        if ($exists > 0) {
            // already have data for this tick

            // ignore pending states for now
            /*           // but it might have been manually entered so
            // there might be pending states still to get
            \DB::transaction(function() use ($influences) {
                foreach ($influences as $influence) {
                    $this->updatePendingStates($influence['faction'], $influence['pending']);
                }
                $this->info("Updated pending states");
                }); */
            if ($latest) {
                // we have data for this tick but the influence values
                // don't match this - double-tick? missed tick? other
                // oddities?
                if(abs($latest->influence - $influences[0]['influence']) > 0.1) {
                    // check for the obvious case - cached data from previous tick
                    $lasttarget = $target->copy()->subDay();
                    $last = Influence::where('system_id', $system->id)
                        ->where('faction_id', $influences[0]['faction']->id)
                        ->where('date', $lasttarget->format("Y-m-d 00:00:00"))
                        ->first();
                    if ($last && abs($last->influence - $influences[0]['influence']) > 0.1) {
                        // also different to previous day's figure
                        

                        /* If the original data came from fairly near
                         * the expected tick time, then this is
                         * probably data obtained between the state
                         * and influence ticks, and should be
                         * overwritten. Otherwise, flag for manual
                         * attention. */
                        if (!\App\Util::fairlyNearTick($latest->created_at->timestamp, 20)) {
                        
                            Alert::alert("EDDN influence discrepancy in ".$system->displayName()." - verify manually.");
                            $this->error("Data discrepancy - verify manually");
                        } else {
                            $overwrite = true;
                            // commented out for now
                            //                            Alert::alert("EDDN influence discrepancy in ".$system->displayName()." - overwriting.");
                            $this->error("Data discrepancy - overwriting");
                            if (!$this->monitoronly) {
                                $reset = Influence::where('system_id', $system->id)
                                       ->where('date', $target->format("Y-m-d 00:00:00"))
                                       ->where('current', 1)
                                       ->delete();
                            }
                        }
                    }
                }
            }
            if (!$overwrite) {
                return;
            }
        }


        if ($latest && !$overwrite) {
            // if not, then new system being read
            if(abs($latest->influence - $influences[0]['influence']) < 0.1) {
                // data is too close to existing data, may be stale
                // usort() in process() above ensures we're looking at
                // the largest one which is most likely to change anyway
                if (\App\Util::fairlyNearTick(null, 4)) {
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
//                $io->state_id = $influence['state']->id;
                $io->influence = $influence['influence'];
                $io->happiness = $influence['happiness'];
                $io->current = 1;
                $io->date = $target;
                $io->save();

                $io->states()->attach($influence['state']->pluck('id')->unique());
            }
            \Log::info("Influence update", [
                'system' => $system->displayName(),
                'user' => "EDDN Feed"
            ]);
            $this->info("Updated influence for ".$system->displayName());

/*            foreach ($influences as $influence) {
                $this->updatePendingStates($influence['faction'], $influence['pending']);
            }
            $this->info("Updated pending states"); */
                            
        });
    }

    /* TODO: this will need updating to be system-specific, but wait
     * to see if it's useful first */
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
            case '$GALAXY_MAP_INFO_state_anarchy;':
            case '$GAlAXY_MAP_INFO_state_anarchy;':
                $system->security = "Anarchy";
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
            if ($station->faction->government->name != "Anarchy") {
                // anarchy have IF even in higher security
                $station->facilities()->detach($broker->id);
            }
        }
    }

    private function addBroker($system) {
        $broker = Facility::where('name', 'Broker')->first();

        foreach ($system->stations as $station) {
            if ($station->stationclass->hasSmall || $station->stationclass->hasMedium || $station->stationclass->hasLarge) {
                /* detach than reattach in case it's already there */
                $station->facilities()->detach($broker->id);
                $station->facilities()->attach($broker->id);
            }
        }
    }

    private function processStationDocking($event) {
        $system = System::where('name', $event['message']['StarSystem'])
            ->orWhere('catalogue', $event['message']['StarSystem'])
            ->first();
        if (!$system) {
            return;
        }
        /* Ignore carriers. As stations already need to be known to
         * accept C/O/S events for them, this will also keep them away
         * from that. */
        if ($event['message']['StationType'] == "FleetCarrier") {
            return;
        }
        
        $station = Station::where('name', $event['message']['StationName'])
            ->where('system_id', $system->id)->first();
        if (!$station) {
            Alert::alert("Unknown station ".$event['message']['StationName']." in ".$system->displayName());
            return;
        }
        $this->line("[".date("YmdHis")."] Docking event for ".$system->displayName().": ".$station->name);

        if (isset($event['message']['StationFaction']) && is_array($event['message']['StationFaction'])) {
            if (strtolower($station->faction->name) != strtolower($event['message']['StationFaction']['Name'])) {
                Alert::alert("Ownership changed ".$station->name." was '".$station->faction->name."' is now '".$event['message']['StationFaction']['Name']."'");
                $faction = Faction::where('name', $event['message']['StationFaction']['Name'])->first();
                if ($faction) {
                    $station->changeOwnership($faction);
                } else {
                    Alert::alert("Unrecognised faction ".$event['message']['StationFaction']['Name']);
                }
                // for now, don't automatically update
            }
        }

        if ($station->stationclass->name == "Small Planetary Factory") {
            // update factory sizes on docking
            if ($event['message']['LandingPads']['Large'] > 0) {
                $class = Stationclass::where('name', 'Large Planetary Factory')->first();
                $station->stationclass_id = $class->id;
            } elseif ($event['message']['LandingPads']['Medium'] > 0) {
                $class = Stationclass::where('name', 'Medium Planetary Factory')->first();
                $station->stationclass_id = $class->id;
            }
        }

        // update distance
        $station->distance = (int)$event['message']['DistFromStarLS'];
        $station->save();
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

        if ($station->faction->virtual) {
            // virtual factions don't have states, so assume None
            $states = collect([1]);
        } else {
            $states = $station->faction->currentStateList($station->system)->pluck('id');
        }
        
        Reserve::where('station_id', $station->id)->update(['current' => false]);

        foreach ($event['message']['commodities'] as $cdata) {
            $commodity = Commodity::firstOrCreate(['name' => $cdata['name']]);
            if ($commodity->averageprice != $cdata['meanPrice']) {
                if ($commodity->averageprice) {
                    Alert::alert("Commodity ".$commodity->name." average price change ".$commodity->averageprice." to ".$cdata['meanPrice']);
                } else {
                    Alert::alert("New commodity ".$commodity->name." detected");
                }
                $commodity->averageprice = $cdata['meanPrice'];
                $commodity->save();
            }

            if ($cdata['stockBracket'] == 0 && $cdata['demandBracket'] == 0) {
                // ignore zero lines
                continue;
            }
            
            $reserve = new Reserve;
            $reserve->current = true;
            $reserve->date = new Carbon($event['message']['timestamp']);
            $reserve->commodity_id = $commodity->id;
            $reserve->station_id = $station->id;
//            $reserve->state_id = $state->id;

            if ($cdata['stock'] > 0) {
                $reserve->reserves = $cdata['stock'];
                $reserve->price = $cdata['buyPrice'];
            } else {
                $reserve->reserves = -$cdata['demand'];
                $reserve->price = $cdata['sellPrice'];
            }
            $reserve->save();
            // sync state list from current influence
            $reserve->states()->attach($states);
        }
    }

    private function processOutfittingEvent($event) {
        if ($event['header']['softwareName'] == "EDDI" && ($event['header']['softwareVersion'] == "3.4.1" || $event['header']['softwareVersion'] == "3.4.2")) {
            // EDDI 3.4.1/3.4.2 gives odd outfitting data at times
            return;
        }
           
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
                $modules[$module->id] = ['current' => true];
                if ($module->stations_count == 0) {
                    // not seen before
                    Alert::alert("Module ".$module->displayName()." now available at ".$station->name);
                }
            }
        }
        /* Use syncWithoutDetaching to avoid people without
         * horizons/Cobra IV/etc. making the availability disappear
         * when it's just that they personally can't see it */

        \DB::transaction(function() use ($station, $modules) { 
            // set availability to false
            \DB::table('module_station')->where('station_id', $station->id)
                                        ->update(['current' => false]);
            // sync modules found now
            $station->modules()->syncWithoutDetaching($modules);
            // any that have been available which aren't now, mark unreliable
            \DB::table('module_station')->where('station_id', $station->id)
                                        ->where('current', false)
                                        ->update(['unreliable' => true]);
        });

    }

    private function processShipyardEvent($event) {
        if ($event['header']['softwareName'] == "EDDI" && ($event['header']['softwareVersion'] == "3.4.1" || $event['header']['softwareVersion'] == "3.4.2")) {
            // EDDI 3.4.1/3.4.2 gives odd outfitting data at times
            return;
        }

	// allowing Odyssey events for now
        
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
        $this->line("[".date("YmdHis")."] Shipyard event for ".$system->displayName().": ".$station->name);

        $ships = [];
        foreach ($event['message']['ships'] as $shipcode) {
            $ship = Ship::where('eddn', $shipcode)->withCount('stations')->first();
            if (!$ship) {
                Alert::alert("Ship code ".$shipcode." not known.");
                continue;
            } else {
                $ships[$ship->id] = ['current' => true];
                if ($ship->stations_count == 0) {
                    // not seen before
                    Alert::alert("Ship ".$ship->name." now available at ".$station->name);
                }
            }
        }
        /* Use syncWithoutDetaching to avoid people without
         * horizons/Cobra IV/etc. making the availability disappear
         * when it's just that they personally can't see it */

        \DB::transaction(function() use ($station, $ships) { 
            // set availability to false
            \DB::table('ship_station')->where('station_id', $station->id)
                                      ->update(['current' => false]);
            // sync modules found now
            $station->ships()->syncWithoutDetaching($ships);
            // any that have been available which aren't now, mark unreliable
            \DB::table('ship_station')->where('station_id', $station->id)
                                      ->where('current', false)
                                      ->update(['unreliable' => true]);
        });

    }

    public function updateConflicts($system, $conflictsdata) {
        // delete previous conflict records
        Conflict::where('system_id', $system->id)->delete();
        
        foreach ($conflictsdata as $conflictdata) {
            $type = ucwords($conflictdata['WarType']);
            $status = ucwords($conflictdata['Status']);
            
            $f1 = Faction::where('name', $conflictdata['Faction1']['Name'])->first();
            $f2 = Faction::where('name', $conflictdata['Faction2']['Name'])->first();
            if (!$f1 || !$f2) {
                Alert::alert("Conflict between ".$conflictdata['Faction1']['Name']." and ".$conflictdata['Faction2']['Name']." in ".$system->displayName()." could not be parsed.");
                continue;
            }
            
            $a1 = $this->assetSearch($system, $conflictdata['Faction1']['Stake']);
            $a2 = $this->assetSearch($system, $conflictdata['Faction2']['Stake']);
            $score = $conflictdata['Faction1']['WonDays']."-".$conflictdata['Faction2']['WonDays'];

            $c = new Conflict;
            $c->system_id = $system->id;
            $c->type = $type;
            $c->status = $status;
            $c->faction1_id = $f1->id;
            $c->faction2_id = $f2->id;
            $c->score = $score;
            if ($a1) {
                $c->asset1()->associate($a1);
                // status='' = recovering, when we might expect a mismatch
                if ($a1->faction_id != $f1->id && $c->status != '') {
                    Alert::alert("Ownership mismatch in conflict for ".$a1->displayName()." in ".$system->displayName());
                    $a1->changeOwnership($f1);
                }
            }
            if ($a2) {
                $c->asset2()->associate($a2);
                if ($a2->faction_id != $f2->id && $c->status != '') {
                    Alert::alert("Ownership mismatch in conflict for ".$a2->displayName()." in ".$system->displayName());
                    $a2->changeOwnership($f2);
                }
            }

            if ($c->status == '') {
                // conflict is over
                if ($conflictdata['Faction1']['WonDays'] > $conflictdata['Faction2']['WonDays'] && $a2 && $a2->faction_id != $f1->id) {
                    // f1 won, transfer a2
                    Alert::alert("Conflict victory transfers ".$a2->displayName()." to ".$f1->name." in ".$system->displayName());
                    $a2->changeOwnership($f1);
                } elseif ($conflictdata['Faction2']['WonDays'] > $conflictdata['Faction1']['WonDays'] && $a1 && $a1->faction_id != $f2->id) {
                    // f2 won, transfer a1
                    Alert::alert("Conflict victory transfers ".$a1->displayName()." to ".$f2->name." in ".$system->displayName());
                    $a1->changeOwnership($f2);
                }
            }
            
            $c->save();
        }
    }

    public function assetSearch($system, $assetname) {
        if ($assetname == "") { return null; }

        $station = Station::where('system_id', $system->id)->where('name', $assetname)->first();
        if ($station) { return $station; }

        $installation = Installation::where('system_id', $system->id)->where('name', $assetname)->first();
        if ($installation) { return $installation; }

        $itype = Installationclass::where('name', substr($assetname,0,strlen($assetname)-strlen(" Installation")))->first();
        if ($itype) {
            $installation = Installation::where('system_id', $system->id)->where('installationclass_id', $itype->id)->first();
            if ($installation) { return $installation; }
        }
        Alert::alert("Unknown asset ".$assetname." in ".$system->displayName());
        return null;
    }
    
}
