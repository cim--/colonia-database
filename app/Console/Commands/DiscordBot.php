<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Faction;
use App\Models\Station;
use App\Models\State;
use App\Models\Influence;
use App\Models\Facility;
use App\Models\Economy;
use App\Models\Government;

class DiscordBot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:discordbot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the discord bot';

    private $discord = null;
    
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
        $this->discord = new \Discord\DiscordCommandClient([
            'token' => env('DISCORD_TOKEN'),
            'description' => "Colonia Census Information Retrieval.\nUse the 'help' command to see a list of commands. You can send commands in chat or by private message.",
            'prefix' => env('DISCORD_COMMAND_PREFIX', '!')
        ]);

        $this->registerSystemCommand();
        $this->registerStationCommand();
        $this->registerFactionCommand();
        $this->registerInfluenceCommand();
        $this->registerReportCommand();
        $this->registerLocateCommand();
        $this->registerExpansionCommand();
        $this->registerExpansionCommand();

        $this->discord->on('ready', function() {
            $game = $this->discord->factory(\Discord\Parts\User\Game::class, [
                'name' => route('index'),
            ]);
            
            $this->discord->updatePresence($game);
        });
        
        $this->discord->run();

    }

    private function safe($str) {
        if (strlen($str) <= 1900) {
            return $str;
        }
        return substr($str, 0, 1900)."...\n**<transmission interrupted>**";
    }
    
    private function registerSystemCommand() {
        $this->discord->registerCommand('system', function($message, $params) {
            $sname = trim(join(" ", $params));
            $system = System::where('name', $sname)->orWhere('catalogue', $sname)->first();
            if (!$system) {
                return $sname." not known";
            } else {
                $result = "**".$system->displayName()."**\n<".route('systems.show', $system->id).">\n";
                if ($system->population == 0) {
                    $result .= "Uninhabited system\n";
                } else {
                    $result .= "**Population**: ".number_format($system->population)."\n**Economy**: ".$system->economy->name."\n";
                    $result .= "**Controlling Faction**: ".$system->controllingFaction()->name." (".$system->controllingFaction()->government->name.")\n";
                    $result .= "**Stations**: ";
                    $idx = 0;
                    foreach ($system->stations->sortBy('name') as $station) {
                        if ($idx++ > 0) {
                            $result .= ", ";
                        }
                        if ($station->primary) {
                            $result .= "*".$station->name."*";
                        } else {
                            $result .= $station->name;
                        }
                        $result .= " (".$station->stationclass->name.", ".$station->economy->name.")";

                    }
                    $result .= "\n";
                }
                $result .= "**Features**: ";
                $idx = 0;
                foreach ($system->facilities->sortBy('name') as $facility) {
                    if ($idx++ > 0) {
                        $result .= ", ";
                    }
                    $result .= $facility->name;
                }
                $result .= "\n";
                if ($system->edsm) {
                    $result .= "<https://www.edsm.net/en/system/id/".$system->edsm."/name/>\n";
                }
                if ($system->eddb) {
                    $result .= "<https://eddb.io/system/".$system->eddb.">\n";
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'Return information about the named system.',
            'usage' => '<system name>',
        ]);
    }

    private function registerStationCommand() {
        $this->discord->registerCommand('station', function($message, $params) {
            $sname = trim(join(" ", $params));
            $station = Station::where('name', $sname)->first();
            if (!$station) {
                return $sname." not known";
            } else {
                $result = "**".$station->name."**\n<".route('stations.show', $station->id).">\n";
                $result .= "**Type**: ".$station->stationclass->name;
                if ($station->primary) {
                    $result .= " (*main station*)";
                }
                $result .= "\n";
                $result .= "**Location**: ".$station->system->displayName()." ".$station->planet." (".$station->distance." Ls)\n";
                
                $result .= "**Economy**: ".$station->economy->name."\n";
                $result .= "**Controlling Faction**: ".$station->faction->name." (".$station->faction->government->name.")\n";                

                $result .= "**Facilities**: ";
                $idx = 0;
                foreach ($station->facilities->sortBy('name') as $facility) {
                    if ($idx++ > 0) {
                        $result .= ", ";
                    }
                    $result .= $facility->name;
                }
                $result .= "\n";
                if ($station->eddb) {
                    $result .= "<https://eddb.io/station/".$station->eddb.">\n";
                } 
                return $this->safe($result);
            }
        }, [
            'description' => 'Return information about the named station.',
            'usage' => '<station name>',
        ]);
    }


    private function registerFactionCommand() {
        $this->discord->registerCommand('faction', function($message, $params) {
            $fname = trim(join(" ", $params));
            $faction = Faction::where('name', $fname)->first();
            if (!$faction) {
                return $fname." not known";
            } else {
                $result = "**".$faction->name."**\n";
                $result .= "<".route('factions.show', $faction->id).">\n";
                $result .= "**Government**: ".$faction->government->name;
                if ($faction->player) {
                    $result .= " (*player faction*)";
                }
                $result .= "\n";
                $result .= "**Home System**: ".$faction->system->displayName()."\n";
                $result .= "**Systems**: ";
                $influences = $faction->latestSystems();
                $idx = 0;
                foreach ($influences as $influence) {
                    if ($idx++ > 0) {
                        $result .= ", ";
                    }
                    $result .= $influence->system->displayName()." (".$influence->influence.", ".$influence->state->name.")";
                }
                $result .= "\n";


                if ($faction->eddb) {
                    $result .= "<https://eddb.io/faction/".$faction->eddb.">\n";
                } 
                return $this->safe($result);
            }
        }, [
            'description' => 'Return information about the named faction.',
            'usage' => '<faction name>',
        ]);
    }

    private function registerInfluenceCommand() {
        $this->discord->registerCommand('influence', function($message, $params) {
            if (preg_match('/^33[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $params[0])) {
                $datestr = array_shift($params);
                $date = new Carbon($datestr);
                $date->subYears(1286);
            } else {
                $date = null;
            }
            $sname = trim(join(" ", $params));
            $system = System::where('name', $sname)->orWhere('catalogue', $sname)->first();
            if (!$system) {
                $faction = Faction::where('name', $sname)->first();
                if (!$faction) {
                    return $sname." not known";
                } else {
                   if ($date !== null) {
                       $result = "**".$faction->name."** on **".\App\Util::displayDate($date)."**\n";
                       $result .= "<".route('factions.showhistory', $faction->id).">\n";

                       $influences = $faction->systems($date);
                       if ($influences->count() == 0) {
                           $result .= "No data for this date";
                           return $this->safe($result);
                       }
                   } else {
                       $influences = $faction->latestSystems();
                       $result = "**".$faction->name."** on **".\App\Util::displayDate($influences[0]->date)."**\n";
                       $result .= "<".route('factions.showhistory', $faction->id).">\n";
                   }
                   foreach ($influences as $influence) {
                       if ($influence->system->controllingFaction()->id == $faction->id) {
                           $result .= "**".$influence->system->displayName()."**";
                       } else {
                           $result .= $influence->system->displayName();
                       }
                       $result .= ": ".$influence->influence."%, ".$influence->state->name;
                       if ($influence->system->id == $faction->system_id) {
                           $result .= " (**home**)";
                       }
                       $result .= "\n";
                   }
                   return $this->safe($result); 
                }
            } else {
                if ($date !== null) {
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($date)."**\n";
                    $result .= "<".route('systems.showhistory', $system->id).">\n";

                    $influences = $system->factions($date);
                    if ($influences->count() == 0) {
                        $result .= "No data for this date";
                        return $this->safe($result);
                    }
                } else {
                    $influences = $system->latestFactions();
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($influences[0]->date)."**\n";
                    $result .= "<".route('systems.showhistory', $system->id).">\n";
                }
                foreach ($influences as $influence) {
                    $result .= $influence->faction->name.": ".$influence->influence."%, ".$influence->state->name."\n";
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'Return influence levels in a system. If the date is omitted will give the latest levels.',
            'usage' => '[yyyy-mm-dd?] <system name>',
            'aliases' => ['politics']
        ]);
    }

    private function registerReportCommand() {
        $this->discord->registerCommand('report', function($message, $params) {
            if (preg_match('/^33[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $params[0])) {
                $datestr = array_shift($params);
                $date = new Carbon($datestr);
                $date->subYears(1286);
            } else {
                $date = null;
            }
            $sname = trim(join(" ", $params));
            $system = System::where('name', $sname)->orWhere('catalogue', $sname)->first();
            if (!$system) {
                return $sname." not known";
            } else {
                if ($date !== null) {
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($date)."**\n";
                    $result .= "<".route('systems.show', $system->id)."#reporthistory>\n";

                    $report = $system->report($date);
                    if (!$report) {
                        $result .= "No data for this date";
                        return $this->safe($result);
                    }
                } else {
                    $report = $system->latestReport();
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($report->date)."**\n";
                    $result .= "<".route('systems.show', $system->id)."#reporthistory>\n";
                }
                $result .= "**Traffic**: ".number_format($report->traffic)."\n";
                $result .= "**Crimes**: ".number_format($report->crime)."\n";
                $result .= "**Bounties**: ".number_format($report->bounties)."\n";
                return $this->safe($result);
            }
        }, [
            'description' => 'Return activity reports for a system. If the date is omitted will give the latest report.',
            'usage' => '[yyyy-mm-dd?] <system name>',
            'aliases' => ['reports', 'traffic', 'crimes', 'bounties']
        ]);
    }

    private function registerLocateCommand() {
        $locate = $this->discord->registerCommand('locate', function($message, $params) {
            return "Use the subcommands to find things - e.g. `locate feature Earth-like World`";
        }, [
            'description' => 'Find systems or stations with particular properties. For all subcommands omit the parameter to get a list of possibilities.'
        ]);
        
        $this->registerLocateFeatureCommand($locate);
        $this->registerLocateFacilityCommand($locate);
        $this->registerLocateEconomyCommand($locate);
        $this->registerLocateGovernmentCommand($locate);
    }

    private function registerLocateFeatureCommand($locate) {
        $locate->registerSubCommand('feature', function ($message, $params) {
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $features = Facility::systemFacilities();
                $result = "**Known features**:\n";
                foreach ($features as $feature) {
                    $result .= $feature->name."\n";
                }
            } else {
                $feature = Facility::where('type', 'System')->where('name', $fname)->first();
                if (!$feature) {
                    $result = "Feature `".$fname."` not known";
                } else {
                    $result = "Systems with **".$feature->name."**\n";
                    
                    $systems = $feature->systems->sortBy('name');
                    foreach ($systems as $system) {
                        $result .= $system->displayName()."\n";
                    }
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Find systems with a particular feature.',
            'usage' => '[feature?]'
        ]);
    }

    private function registerLocateFacilityCommand($locate) {
        $locate->registerSubCommand('facility', function ($message, $params) {
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $features = Facility::stationFacilities();
                $result = "**Known facilities**:\n";
                foreach ($features as $feature) {
                    $result .= $feature->name."\n";
                }
            } else {
                $feature = Facility::where('type', 'Station')->where('name', $fname)->first();
                if (!$feature) {
                    $result = "Feature `".$fname."` not known";
                } else {
                    $result = "Stations with **".$feature->name."**\n";
                    
                    $stations = $feature->stations->sortBy('name');
                    foreach ($stations as $station) {
                        $result .= $station->name." (".$station->system->displayName().")\n";
                    }
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Find stations with a particular facility.',
            'usage' => '[facility?]'
        ]);
    }

    private function registerLocateEconomyCommand($locate) {
        $locate->registerSubCommand('economy', function ($message, $params) {
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $economies = Economy::orderBy('name')->get();
                $result = "**Known economies**:\n";
                foreach ($economies as $economy) {
                    $result .= $economy->name."\n";
                }
            } else {
                $economy = Economy::where('name', $fname)->first();
                if (!$economy) {
                    $result = "Economy `".$economy."` not known";
                } else {
                    $result = "Stations with **".$economy->name."** economy\n";
                    
                    $stations = $economy->stations()->with('stationclass')->orderBy('name')->get();
                    foreach ($stations as $station) {
                        if ($station->stationclass->hasSmall) {
                            $result .= $station->name." (".$station->system->displayName().")\n";
                        }
                    }
                    $result .= "\nSettlements with **".$economy->name."** economy\n";
                    
                    foreach ($stations as $station) {
                        if (!$station->stationclass->hasSmall) {
                            $result .= $station->name." (".$station->system->displayName().")\n";
                        }
                    }
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Find stations with a particular economy.',
            'usage' => '[economy?]'
        ]);
    }


    private function registerLocateGovernmentCommand($locate) {
        $locate->registerSubCommand('government', function ($message, $params) {
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $governments = Government::orderBy('name')->get();
                $result = "**Known governments**:\n";
                foreach ($governments as $government) {
                    $result .= $government->name."\n";
                }
            } else {
                $government = Government::where('name', $fname)->first();
                if (!$government) {
                    $result = "Government `".$government."` not known";
                } else {
                    $result = "Stations with **".$government->name."** government\n";
                    
                    $stations = Station::with('faction')->orderBy('name')->get();
                    foreach ($stations as $station) {
                        if ($station->faction->government_id == $government->id) {
                            if ($station->stationclass->hasSmall) {
                                if ($station->primary) {
                                    $result .= "*".$station->name."* (".$station->system->displayName().")\n";
                                } else {
                                    $result .= $station->name." (".$station->system->displayName().")\n";
                                }
                            }
                        }
                    }
                    $result .= "\nSettlements with **".$government->name."** government\n";
                    
                    foreach ($stations as $station) {
                        if ($station->faction->government_id == $government->id) {
                            if (!$station->stationclass->hasSmall) {
                                $result .= $station->name." (".$station->system->displayName().")\n";
                            }
                        }
                    }
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Find stations with a particular government.',
            'usage' => '[government?]'
        ]);
    }

    private function registerExpansionCommand() {
        $this->discord->registerCommand('expansion', function($message, $params) {

            $str = trim(join(" ", $params));
            if (strpos($str, ";")) {
                list($faction, $system) = explode(";", $str);
            } else {
                list($faction, $system) = [$str, ''];
            }
            $fname = trim($faction);
            $sname = trim($system);

            $faction = Faction::where('name', $fname)->first();
            if (!$faction) {
                return "Faction ".$fname." not found";
            }
            if ($sname == "") {
                $system = $faction->system;
            } else {
                $system = System::where('name', $sname)->orWhere('catalogue', $sname)->first();
                if (!$system) {
                    return "System ".$sname." not found";
                }
            }

            $systems = System::all();
            $peacefulcandidates = [];
            $aggressivecandidates = [];
            foreach ($systems as $target) {
                if ($target->id == $system->id) {
                    continue;
                }
                if ($target->population == 0) {
                    continue;
                }
                if ($target->name == "Ratri" || $target->name == "Colonia") {
                    continue; // locked systems
                }
                if ($faction->currentInfluence($target) !== null) {
                    continue;
                }
                if ($target->distanceTo($system) > 30) {
                    continue;
                }
                if ($target->latestFactions()->count() >= 7) {
                    $aggressivecandidates[] = $target;
                } else {
                    $peacefulcandidates[] = $target;
                }
            }
            $sorter = function($a, $b) use ($system) {
                return $this->sign($a->distanceTo($system)-$b->distanceTo($system));
            };
            
            usort($aggressivecandidates, $sorter);
            usort($peacefulcandidates, $sorter);

            $result = "**Expansion candidates** for **".$fname."** from **".$system->displayName()."**\n";
            $nearfound = false;
            for ($i=0;$i<=2;$i++) {
                if (isset($peacefulcandidates[$i])) {
                    $dist = $peacefulcandidates[$i]->distanceTo($system);
                    if ($dist < 22) {
                        $nearfound = true;
                    }
                    $result .= $peacefulcandidates[$i]->displayName()." (".number_format($dist,2)."LY)\n";
                }
            }
            if (!$nearfound && count($aggressivecandidates) > 0) {
                $result .= "\nAs all candidates are likely to require *Investment*, an aggressive expansion is also possible:\n";
                for ($i=0;$i<=2;$i++) {
                    if (isset($aggressivecandidates[$i])) {
                        $dist = $aggressivecandidates[$i]->distanceTo($system);
                        $result .= $aggressivecandidates[$i]->displayName()." (".number_format($dist,2)."LY)\n";
                    }
                }
            }
            
            return $result;

        }, [
            'description' => 'Give likely expansion targets for a faction, defaulting to home system if not specified.',
            'usage' => '<faction> [; system?]'
        ]);
        
    }

    private function sign($a) {
        if($a > 0) { return 1; }
        if($a < 0) { return -1; }
        return 0;
    }
}
