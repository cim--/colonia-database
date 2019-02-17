<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Faction;
use App\Models\Station;
use App\Models\State;
use App\Models\Influence;
use App\Models\Facility;
use App\Models\Economy;
use App\Models\Government;
use App\Models\History;
use App\Models\Expansioncache;
use App\Models\Megaship;
use App\Models\Installation;
use App\Models\Project;
use App\Models\Objective;
use App\Models\Contribution;

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
        $this->discord = new \App\DiscordClient([
            'token' => env('DISCORD_TOKEN'),
            'description' => "Colonia Census Information Retrieval.\nUse the 'help' command to see a list of commands. You can send commands in chat or by private message.",
            'prefix' => env('DISCORD_COMMAND_PREFIX', '!'),
            'defaultHelpCommand' => false,
            'discordOptions' => [
                'disabledEvents' => ['PRESENCE_UPDATE'] // don't use it and the implementation can cause the client to crash
            ]
        ]);

        $this->registerHelpCommand();
        $this->registerSystemCommand();
        $this->registerStationCommand();
        $this->registerFactionCommand();
        $this->registerInfluenceCommand();
        $this->registerMegashipCommand();
        $this->registerInstallationCommand();
        $this->registerReportCommand();
        $this->registerLocateCommand();
        $this->registerExpansionCommand();
        $this->registerExpansionsToCommand();
        $this->registerMissionsCommand();
        $this->registerCartographyCommand();
        $this->registerSummaryCommand();
        $this->registerHistoryCommand();
        $this->registerAddReportCommand();
        $this->registerProjectCommand();
        $this->registerContributionCommand();
        
        $this->discord->on('ready', function() {
            $game = $this->discord->factory(\Discord\Parts\User\Game::class, [
                'name' => route('index'),
            ]);
            
            $this->discord->updatePresence($game);
        });
        
        $this->discord->run();

    }

    private function safe($str) {
        $str = str_replace("@", "", $str);
        if (strlen($str) <= 1900) {
            return $str;
        }
        return substr($str, 0, 1900)."...\n**<transmission interrupted>**";
    }

    private function syntaxCheck($params) {
        foreach ($params as $param) {
            if (strpos($param, '@') !== false) {
                throw new Exception("Syntax check");
            }
        }
    }
    
    private function registerHelpCommand() {
        $this->discord->registerCommand('help', function($message, $params) {
            $this->syntaxCheck($params);
            $prefix = env('DISCORD_COMMAND_PREFIX', '!');
            $commandopts = $this->discord->getCommandOptions();
            $commands = $this->discord->getCommands();
            if (!isset($params[0])) {
                $result = "**CensusBot: Colonia Census Information Retrieval.**\nYou can send commands in chat or by private message.\nUse `".$prefix."help <command name>` for more information on a command.\n\n```\n";
                foreach ($commandopts as $name => $cobj) {
                    if (isset($cobj['usage'])) {
                        $result .= $prefix.$name." ".$cobj['usage']."\n";
                    } else {
                        $result .= $prefix.$name."\n";
                    }
                }
                $result .= "```";
                $result .= "Read the bot privacy policy - <https://cdb.sotl.org.uk/about#bpp> - before using `!addreport` or `!contribute`";
                                
            } else {
                if (isset($commands[$params[0]])) {
                    $help = $commands[$params[0]]->getHelp($prefix);
                    $result = "```\n".$help['text']."\n```";
                } else {
                    $result = "Command `".$params[0]."` not known.\n";
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Display command help.',
            'usage' => '[command?]',
        ]);
    }
    
    private function registerSystemCommand() {
        $this->discord->registerCommand('system', function($message, $params) {
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known");
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
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            $station = Station::where('name', 'like', $sname."%")->orderBy('name')->first();
            if (!$station) {
                return $this->safe($sname." not known");
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
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            $faction = Faction::where('name', 'like', $fname."%")->orderBy('name')->first();
            if (!$faction) {
                return $this->safe($fname." not known");
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
                    $result .= $influence->system->displayName()." (".$influence->influence.", ".$influence->states->implode('name', ', ').")";
                }
                $result .= "\n";
                $result .= "**Assets controlled**: ".Station::where('faction_id', $faction->id)->count()."\n";

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
            $this->syntaxCheck($params);
            if (preg_match('/^33[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $params[0])) {
                $datestr = array_shift($params);
                $date = new Carbon($datestr);
                $date->subYears(1286);
            } else {
                $date = null;
            }
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                $faction = Faction::where('name', 'like', $sname."%")->orderBy('name')->first();
                if (!$faction) {
                    return $this->safe($sname." not known");
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
                       $result .= "`[";
                       $bar = 1+floor($influence->influence/5);
                       $result .= str_repeat("█", $bar);
                       $result .= str_repeat(" ", 20-$bar);
                       $result .= "]` ";
                       if ($influence->system->controllingFaction()->id == $faction->id) {
                           $result .= "**".$influence->system->displayName()."**";
                       } else {
                           $result .= $influence->system->displayName();
                       }
                       $result .= ": ".$influence->influence."%, ".$influence->states->implode('name', ', ');
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
                    $result .= "`[";
                    $bar = 1+floor($influence->influence/5);
                    $result .= str_repeat("█", $bar);
                    $result .= str_repeat(" ", 20-$bar);
                    $result .= "]` ";
                    if ($system->controllingFaction()->id == $influence->faction->id) {
                        $result .= "**".$influence->faction->name."**";
                    } else {
                        $result .= $influence->faction->name;
                    }
                    $result .= ": ".$influence->influence."%, ".$influence->states->implode('name', ', ');
                    if ($system->id == $influence->faction->system_id) {
                        $result .= " (**home**)";
                    }
                    $result .= "\n";
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'Return influence levels in a system. If the date is omitted will give the latest levels.',
            'usage' => '[yyyy-mm-dd?] <system name|faction name>',
            'aliases' => ['politics']
        ]);
    }

    private function registerReportCommand() {
        $this->discord->registerCommand('report', function($message, $params) {
            $this->syntaxCheck($params);
            if (preg_match('/^33[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $params[0])) {
                $datestr = array_shift($params);
                $date = new Carbon($datestr);
                $date->subYears(1286);
            } else {
                $date = null;
            }
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known");
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
            $this->syntaxCheck($params);
            return $this->safe("Use the subcommands to find things - e.g. `locate feature Earth-like World`");
        }, [
            'description' => 'Find systems or stations with particular properties. For all subcommands omit the parameter to get a list of possibilities.',
            'usage' => '<feature | facility | economy | government | state> [name?]'
        ]);
        
        $this->registerLocateFeatureCommand($locate);
        $this->registerLocateFacilityCommand($locate);
        $this->registerLocateEconomyCommand($locate);
        $this->registerLocateGovernmentCommand($locate);
        $this->registerLocateStateCommand($locate);
    }

    private function registerLocateFeatureCommand($locate) {
        $locate->registerSubCommand('feature', function ($message, $params) {
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $features = Facility::systemFacilities();
                $result = "**Known features**:\n";
                foreach ($features as $feature) {
                    $result .= $feature->name."\n";
                }
            } else {
                $feature = Facility::where('type', 'System')->where('name', 'like', $fname."%")->orderBy('name')->first();
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
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $features = Facility::stationFacilities();
                $result = "**Known facilities**:\n";
                foreach ($features as $feature) {
                    $result .= $feature->name."\n";
                }
            } else {
                $feature = Facility::where('type', 'Station')->where('name', 'like', $fname."%")->orderBy('name')->first();
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
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $economies = Economy::orderBy('name')->get();
                $result = "**Known economies**:\n";
                foreach ($economies as $economy) {
                    $result .= $economy->name."\n";
                }
            } else {
                $economy = Economy::where('name', 'like', $fname."%")->orderBy('name')->first();
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
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $governments = Government::orderBy('name')->get();
                $result = "**Known governments**:\n";
                foreach ($governments as $government) {
                    $result .= $government->name."\n";
                }
            } else {
                $government = Government::where('name', 'like', $fname."%")->orderBy('name')->first();
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

    
    private function registerLocateStateCommand($locate) {
        $locate->registerSubCommand('state', function ($message, $params) {
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            if ($fname == "") {
                $states = State::orderBy('name')->get();
                $result = "**Known states**:\n";
                foreach ($states as $state) {
                    $result .= $state->name."\n";
                }
            } else {
                $state = State::where('name', 'like', $fname."%")->orderBy('name')->first();
                if (!$state) {
                    $result = "State `".$state."` not known";
                } else {
                    $result = "Stations with **".$state->name."** state\n";
                    
                    $stations = Station::with('faction','system')->whereHas('faction', function($q) use ($state) {
                        $q->whereHas('influences', function ($qi) use ($state) {
                            $qi->where('current', 1)
                               ->where('system_id', \DB::raw('`stations`.`system_id`'))
                               ->whereHas('states', function ($qs) use ($state) {
                                   $qs->where('states.id', $state->id);
                               });
                        });
                    })->orderBy('name')->get();
                    foreach ($stations as $station) {
                        if ($station->stationclass->hasSmall) {
                            if ($station->primary) {
                                $result .= "*".$station->name."* (".$station->system->displayName().")\n";
                            } else {
                                $result .= $station->name." (".$station->system->displayName().")\n";
                            }
                        }
                    }
                }
            }
            return $this->safe($result);
        }, [
            'description' => 'Find stations with a particular state. (War finds both War and Civil War)',
            'usage' => '[state?]'
        ]);
    }

    private function registerExpansionCommand() {
        $this->discord->registerCommand('expansion', function($message, $params) {
            $this->syntaxCheck($params);

            $str = trim(join(" ", $params));
            if (strpos($str, ";")) {
                list($faction, $system) = explode(";", $str);
            } else {
                list($faction, $system) = [$str, ''];
            }
            $fname = trim($faction);
            $sname = trim($system);

            $system = null;
            $faction = Faction::where('name', 'like', $fname."%")->orderBy('name')->first();
            if (!$faction) {
                if ($sname == "") {
                    $system = System::where('name', 'like', $fname."%")->orWhere('catalogue', 'like', $fname."%")->orderBy('name')->first();
                    if (!$system) {
                        return $this->safe("Faction ".$fname." not found");
                    } else {
                        $faction = $system->controllingFaction();
                    }
                } else {
                    return $this->safe("Faction ".$fname." not found");
                }
            }
            if (!$system) {
                if ($sname == "") {
                    $system = $faction->system;
                } else {
                    $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
                    if (!$system) {
                        return $this->safe("System ".$sname." not found");
                    }
                }
            }
            if ($faction->virtual) {
                return $this->safe($faction->name." is virtual and cannot expand.");
            }
            

            list ($peacefulcandidates, $aggressivecandidates) = $system->expansionsFor($faction);

            $result = "**Expansion candidates** for **".$faction->name."** from **".$system->displayName()."**\n";
            $nearfound = false;
            $retreatnote = false;
            $investnote = false;
            $eightnote = false;
            for ($i=0;$i<=3;$i++) {
                if (isset($peacefulcandidates[$i])) {
                    $dist = $peacefulcandidates[$i]->distanceTo($system);
                    $result .= $peacefulcandidates[$i]->displayName()." (".number_format($dist,2)."LY";
                    if ($faction->previouslyIn($peacefulcandidates[$i])) {
                        $result .= " ⏪";
                        $retreatnote = true;
                    }
                    if ($system->expansionCube($peacefulcandidates[$i], 20)) {
                        $nearfound = true;
                    } else {
                        $result .= " ⏩";
                        $investnote = true;
                    }
                    $result .= ")\n";
                }
            }
            if (!$nearfound && count($aggressivecandidates) > 0) {
                $result .= "\nAs all candidates are likely to require *increased range*, an aggressive expansion is also possible:\n";
                for ($i=0;$i<=3;$i++) {
                    if (isset($aggressivecandidates[$i])) {
                        $dist = $aggressivecandidates[$i]->distanceTo($system);
                        $result .= $aggressivecandidates[$i]->displayName()." (".number_format($dist,2)."LY";
                        if ($faction->previouslyIn($aggressivecandidates[$i])) {
                            $result .= " ⏪";
                            $retreatnote = true;
                        }
                        if (!$system->expansionCube($aggressivecandidates[$i], 20)) {
                            $result .= " ⏩";
                            $investnote = true;
                        }
                        if ($aggressivecandidates[$i]->latestFactions()->count() > 7) {
                            $result .= " †";
                            $eightnote = true;
                        }
                        $result .= ")\n";
                    }
                }
                $result .= "Aggressive expansion destinations may be unpredictable due to the need for a suitable target faction.\n";
            }

            if ($retreatnote) {
                $result .= "\n⏪ indicates a previous retreat - the faction may skip this system.";
            }
            if ($investnote) {
                $result .= "\n⏩ indicates increased range from a previous failed expansion is required to expand here.";
            }
            if ($eightnote) {
                $result .= "\n† aggressive expansion here may not be possible as system already has 8 factions";
            }
            
            return $this->safe($result);

        }, [
            'description' => 'Give likely expansion targets for a faction, defaulting to home system if not specified, or for a system and its controlling faction',
            'usage' => '(<faction> [; system?]) | <system>',
            'aliases' => ['expansions']
        ]);
        
    }

    private function sign($a) {
        if($a > 0) { return 1; }
        if($a < 0) { return -1; }
        return 0;
    }

    private function registerMissionsCommand() {
        $this->discord->registerCommand('missions', function($message, $params) {
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known");
            } else {
                $result = "**Mission** destinations from **".$system->displayName()."**\n<".route('systems.show', $system->id).">\n";

                $systems = System::all();
                $destinations = [];
                foreach ($systems as $target) {
                    if ($target->id == $system->id) {
                        continue;
                    }
                    if ($target->population == 0) {
                        continue;
                    }
                    if (in_array($target->displayName(), ["Colonia", "Ratraii", "Ogmar", "Tenjin"])) {
                        // always a possible destination for medium range
                        $destinations[] = $target;
                        continue;
                    }
                    if ($target->distanceTo($system) > 15) {
                        continue;
                    }
                    $destinations[] = $target;
                }

                if (count($destinations) > 0) {
                    $sorter = function($a, $b) use ($system) {
                        return $this->sign($a->distanceTo($system)-$b->distanceTo($system));
                    };
                    usort($destinations, $sorter);
                    foreach ($destinations as $destination) {
                        $dist = $destination->distanceTo($system);
                        $result .= $destination->displayName()." (".number_format($dist,2)."LY)\n";
                    }
                    $result .= "(Some destinations may have no missions in practice due to unmet prerequisites)\n";
                } else {
                    $result .= "No inhabited systems within 15 LY\n";
                }

                if ($system->economy->name == "Tourism") {
                    $result .= "\nInter-bubble courier or passenger missions to Sol bubble destinations are sometimes possible.";
                }
                
                return $this->safe($result);
            }
        }, [
            'description' => 'Return likely mission destinations.',
            'usage' => '<system name>',
            'aliases' => ['mission']
        ]);
    }

    private function registerCartographyCommand() {
        $this->discord->registerCommand('cartography', function($message, $params) {
            $this->syntaxCheck($params);
            if (count($params) < 3) {
                return $this->safe("All three parameters are required:\n<max-gravity> (use 0 for orbital stations only)\n<pad-size> (S, M, or L)\n<max-dist> (Ls from primary star)");
            }
            $grav = (float)$params[0];
            $pad = $params[1];
            $dist = (float)$params[2];

            $options = Station::where(function($q) use ($grav) {
                $q->where('gravity', null)
                  ->orWhere('gravity', '<=', $grav);
            })->whereHas('stationclass', function($q) use ($pad) {
                $q->where('hasLarge', 1);
                if ($pad != 'L') {
                    $q->orWhere('hasMedium', 1);
                    if ($pad != 'M') {
                        $q->orWhere('hasSmall', 1);
                    }
                }
            })->where('distance', '<=', $dist)
              ->whereHas('enabledFacilities', function($q) {
                  $q->where('name', 'Cartographics');
              })->with('system', 'faction', 'stationclass')->orderBy('name')->get();

            $result = "**Exploration sale options**\nMax gravity: ".($grav>0?number_format($grav,2):"orbital only")."\nPad size: $pad\nMax dist: ".number_format($dist)."Ls\n\n";
            foreach ($options as $option) {
                $result .= $option->name.", ".$option->system->displayName()." (".$option->faction->name." - ";
/*                $states = $option->faction->currentStates();
                $commas = false;
                foreach ($states as $idx => $state) {
                    if ($commas) {
                        $result .= ", ";
                    } else {
                        $commas = true;
                    }
                    if (in_array($state->name, ["Expansion", "Investment", "War", "Lockdown"])) {
                        $result .= "**".$state->name."**";
                    } else {
                        $result .= $state->name;
                    }
                    } */
                $result .= ") [".($option->gravity?number_format($option->gravity,2)."G":"Orbital").", ".$option->stationclass->name.", ".number_format($option->distance)."Ls]\n";
            }

            return $this->safe($result);
        }, [
            'description' => 'Return possible exploration data sale points. e.g. cartography 0.5 L 1000',
            'usage' => '<max-gravity> <pad-size> <max-dist>',
            'aliases' => ['exploration']
        ]);
        
    }


    private function registerSummaryCommand() {
        $this->discord->registerCommand('summary', function($message, $params) {
            $this->syntaxCheck($params);
            if (count($params) == 0) {
                $params = [""];
            }
            switch ($params[0]) {
            case "population":
                $result = "Total population is ".number_format(System::sum('population'));
                break;
            case "traffic":
                $result = "Total traffic is ".number_format(Systemreport::where('current', 1)->sum('traffic'));
                break;
            case "crime":
            case "crimes":
                $result = "Total daily crimes are ".number_format(Systemreport::where('current', 1)->sum('crime'));
                break;
            case "bounty":
            case "bounties":
                $result = "Total daily bounties are ".number_format(Systemreport::where('current', 1)->sum('bounties'));
                break;
            case "system":
            case "systems":
                $potential = System::where('population', 0)->count();
                $populated = System::where('population', '>', 0)->count();
                $result = $populated." inhabited systems are tracked.";
                if ($potential > 0) {
                    $result .= " ".$potential." systems have been marked as future colonisation locations.";
                }
                break;
            case "station":
            case "stations":
                $stations = Station::whereHas('stationclass', function($q) {
                    $q->where('hasSmall', 1);
                })->count();
                $settlements = Station::whereHas('stationclass', function($q) {
                    $q->where('hasSmall', 0);
                })->count();
                $result = $stations." stations and ".$settlements." settlements";
                break;
            case "economies":
            case "economy":
                $economies = Economy::withCount(['stations' => function($q) {
                    $q->whereHas('stationclass', function($qi) {
                        $qi->where('hasSmall', 1);
                    });
                }])->orderBy('name')->get();
                $result = "Station economies\n";
                foreach ($economies as $economy) {
                    $result .= $economy->name.": ".$economy->stations_count."\n";
                }
                break;
            case "governments":
            case "government":
                $governments = Government::withCount(['factions'])->orderBy('name')->get();
                $result = "Faction governments\n";
                foreach ($governments as $government) {
                    $result .= $government->name.": ".$government->factions_count."\n";
                }
                break;
/*            case "state":
            case "states":
                $states = State::where('name', '!=', 'None')->orderBy('name')->get();
                $result = "Faction states\n";
                foreach ($states as $state) {
                    $result .= $state->name.": ".$state->currentFactions()->get()->count()."\n";
                }
                break; */ // not very useful with multistate
            case "reach":
                $reaches = \DB::select('SELECT f.name, FLOOR(SUM(i.influence/100 * s.population)) AS reach FROM factions f INNER JOIN influences i ON (f.id = i.faction_id) INNER JOIN systems s ON (s.id = i.system_id) WHERE i.current = 1 GROUP BY f.name ORDER BY reach DESC LIMIT 10');
                $result = "Top 10 reaches:\n";
                foreach ($reaches as $reach) {
                    $result .= $reach->name.": ".number_format($reach->reach)."\n";
                }
                break;
            default:
                $result = "Unrecognised summary request";
            }
            
            return $this->safe($result);
        }, [
            'description' => 'Return summaries of information. Available summaries are: population, traffic, crimes, bounties, systems, stations, economy, government and reach',
            'usage' => '<summary>'
        ]);
        
    }

    private function registerHistoryCommand() {
        $this->discord->registerCommand('history', function ($message, $params) {
            $this->syntaxCheck($params);
            $fname = trim(join(" ", $params));
            
            $query = History::with('faction','location');

            if ($fname == "") {
                $date = \App\Util::tick();
                $query->where('date', $date);
                $hname = "current tick";
            } else if (preg_match('/^33[0-9][0-9]-[0-9][0-9]-[0-9][0-9]$/', $fname)) {
                $datestr = $fname;
                $date = new Carbon($datestr);
                $date->subYears(1286);
                $query->where('date', $date);
                $hname = "date ".$fname;
            } else if ($system = System::where('name', 'like', $fname."%")->orWhere('catalogue', 'like', $fname."%")->orderBy('name')->first()) {
                $query->where('location_type', 'App\\Models\\System')
                      ->where('location_id', $system->id);
                $hname = "system ".$system->displayName();
            } else if ($station = Station::where('name', 'like', $fname."%")->orderBy('name')->first()) {
                $query->where('location_type', 'App\\Models\\Station')
                      ->where('location_id', $station->id);
                $hname = "station ".$station->name;
            } else if ($faction = Faction::where('name', 'like', $fname."%")->orderBy('name')->first()) {
                $query->whereHas('faction', function($q) use ($faction) {
                    $q->where('id', $faction->id);
                });
                $hname = "faction ".$faction->name;
            } else {
                return $this->safe("History query parameter not recognised as a date, faction, station or system");
            }

            $histories = $query->orderBy('date', 'desc')->get();
            $result = "**History for ".$hname."**\n";
            foreach ($histories as $history) {
                $result .= \App\Util::displayDate($history->date).": ".$history->faction->name." ".$history->description." ".$history->location->displayName()."\n";
            }
            return $this->safe($result);
        }, [
            'description' => 'Return history entries for the current tick, or the specified date, system, faction or station.',
            'usage' => '[YYYY-MM-DD | station | system | faction]?'
        ]);
    }   

    private function registerExpansionsToCommand() {
        $this->discord->registerCommand('expansionsto', function($message, $params) {
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known");
            } else {
                $result = "**Possible expansions to ".$system->displayName()."**\n";
                $retreatnote = false;
                $investnote = false;
                if ($system->population == 0) {
                    $result .= "Uninhabited system.\n";
                } else {
                    $opts = Expansioncache::where('target_id', $system->id)->orderBy('priority')->with('system')->get();
                    if (count($opts) == 0) {
                        $result .= "No candidates for near-term expansion to this system.";
                    } else {
                        foreach ($opts as $opt) {
                            $result .= "Priority ".$opt->priority." ";
                            if ($opt->hostile) {
                                $result .= "*aggressive* ";
                            }
                            $result .= "target of ".$opt->system->controllingFaction()->name." from ".$opt->system->displayName()." (".number_format($opt->system->distanceTo($system), 2)." LY";
                            if ($opt->previousretreat) {
                                $result .= " ⏪";
                                $retreatnote = true;
                            }
                            if ($opt->investment) {
                                $result .= " ⭲";
                                $investnote = true;
                            }
                            $result .= ")\n";
                        }
                    }

                }

                if ($retreatnote) {
                    $result .= "\n⏪ indicates a previous retreat - the faction may skip this system.";
                }
                if ($investnote) {
                    $result .= "\n⭲ indicates investment is required to expand here.";
                }

                return $this->safe($result);
            }
        }, [
            'description' => 'Return factions which might expand to the specified system soon.',
            'usage' => '<system name>',
            'aliases' => ['expandto', 'expansionto', 'expandsto']
        ]);
    }

    private function registerAddreportCommand() {
        $this->discord->registerCommand('addreport', function($message, $params) {
            $this->syntaxCheck($params);
            $str = trim(join(" ", $params));
            if (count(explode(";", $str)) != 4) {
                return $this->safe("All four parameters separated by ; are required");
            }
            list ($sname, $traffic, $crimes, $bounties) = explode(";", $str);
            $traffic = trim($traffic);
            $crimes = trim($crimes);
            $bounties = trim($bounties);
//            return "[$sname] [$traffic] [$crimes] [$bounties]";
            if (!is_numeric($traffic) || !is_numeric($crimes) || !is_numeric($bounties)) {
                return $this->safe("Traffic, crimes and bounties must all be numeric");
            }
            if ($traffic < 0 || $crimes < 0 || $bounties < 0) {
                return $this->safe("Traffic, crimes and bounties must all be positive or zero");
            }
            $system = System::where('name', $sname)->orWhere('catalogue', $sname)->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known (must be exact)");
            }

            Systemreport::file($system, $traffic, $bounties, $crimes, "via Discord", false);


            return $this->safe("Reports added for ".$system->displayName().". Thank you.");
        }, [
            'description' => "Add traffic, crimes and bounties reports for today to a system.\nYou can get these reports from the local Galnet when docked at a station. Bounties should be the credit total, not the number of bounties collected.\ne.g. addreport Barnard's Star ; 182 ; 402752 ; 76399",
            'usage' => '<system name> ; <traffic> ; <crimes> ; <bounties>'
        ]);
    }

    private function registerMegashipCommand() {
        $this->discord->registerCommand('megaship', function($message, $params) {
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            if ($sname != "") {
                $megaship = Megaship::where('serial', 'like', $sname."%")->orderBy('serial')->first();
            } else {
                $megaship = false;
            }
            if (!$megaship) {
                $result = "";
                if ($sname != "") {
                    $result .= $sname." not known.\n";
                }
                $result .= "**Known megaships:**\n";
                $ships = Megaship::orderBy('serial')->get();
                foreach($ships as $ship) {
                    $result .= $ship->serial."\n";
                }
                return $this->safe($result);
            } else {
                $result = "**".$megaship->displayName()."**\n<".route('megaships.show', $megaship->id).">\n";
                if ($megaship->megashiprole && !$megaship->decommisioned) {
                    $result .= "Role: ".$megaship->megashiprole->name."\n";
                }
                $routes = [];
                foreach ($megaship->megashiproutes as $route) {
                    $rdesc = "";
                    if ($route->system_id) {
                        $rdesc .= $route->system->displayName();
                    } else {
                        $rdesc .= $route->systemdesc;
                    }
                    $rdesc .= ": ";
                    $from = $route->nextArrival();
                    $to = $route->nextDeparture();
                    if ($megaship->megashipclass->operational) {
                        if ($from) {
                            $rdesc .= \App\Util::displayDate($from)." to ".\App\Util::displayDate($to);
                        } else {
                            $rdesc .= "Departing ".\App\Util::displayDate($to);
                        }
                    } else {
                        $rdesc .= "Not operational";
                    }
                    $routes[$to->format("Ymd")] = $rdesc;
                }
                ksort($routes);
                $result .= join("\n",$routes);
                return $this->safe($result);
            }
        }, [
            'description' => 'Return information about the named megaship.',
            'usage' => '<megaship serial number>',
        ]);
    }            


    private function registerInstallationCommand() {
        $this->discord->registerCommand('installation', function($message, $params) {
            $this->syntaxCheck($params);
            $sname = trim(join(" ", $params));
            $system = System::where('name', 'like', $sname."%")->orWhere('catalogue', 'like', $sname."%")->orderBy('name')->first();
            if (!$system) {
                return $this->safe($sname." not known");
            } else {
                $result = "**Installations for ".$system->displayName()."**\n<".route('systems.show', $system->id).">\n";
                $installations = $system->installations;
                if ($installations->count() == 0) {
                    $result .= "No installations in this system.";
                } else {
                    foreach ($installations as $installation) {
                        $result .= $installation->installationclass->name." at ".$installation->planet;
                        if ($installation->name) {
                            $result .= " (".$installation->name.")";
                        }
                        $result .= "\n";
                    }
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'List installations in the named system.',
            'usage' => '<system name>',
            'aliases' => ['installations']
        ]);
    }

    private function registerProjectCommand() {
        $this->discord->registerCommand('project', function($message, $params) {
            $this->syntaxCheck($params);
            $project = false;

            if (isset($params[0])) {
                $project = Project::where('code', '=', $params[0])->first();
            }
            if (!$project) {
                $result = "**Current projects:**\n";
                $projects = Project::orderBy('priority')->where('complete', false)->get();
                foreach($projects as $project) {
                    $result .= '**'.$project->code."**: ".$project->summary."\n";
                }
                return $this->safe($result);
            } else {
                $result = "**".$project->summary."**\n<".route('projects.show', $project->id).">\n";
                if ($project->objectives->count() > 0) {
                    foreach ($project->objectives as $objective) {
                        $result .= "**".$objective->code."**: ".$objective->label."\n";
                        $result .= "... ".number_format($objective->contributions->sum('amount'))." / ".($objective->target?number_format($objective->target):"???");
                        if ($objective->target && $objective->target <= $objective->contributions->sum('amount')) {
                            $result .= " - **Objective completed!**";
                        }
                        $result .= "\n\n";
                    }
                } else {
                    $result .= "No objectives";
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'Return information about a project.',
            'usage' => '<project code>',
            'aliases' => ['projects']
        ]);
    }     

    private function registerContributionCommand() {
        $this->discord->registerCommand('contribute', function($message, $params) {
            $this->syntaxCheck($params);
            if (count($params) < 3) {
                return $this->safe("Usage: !contribute <project code> <objective code> <amount>");
            } else {
                $objective = Objective::where('code', $params[1])->whereHas('project', function($q) use ($params) {
                    $q->where('code', $params[0]);
                })->first();
                
                if (!$objective) {
                    return $this->safe("Objective ".$params[1]." not found on project ".$params[0]);
                }

                $project = $objective->project;

                if ($project->complete) {
                    return $this->safe("This project has been completed");
                }

                if ($params[2] < 1) {
                    return $this->safe("You must contribute at least 1 unit");
                }

                
                $contribution = new Contribution;
                $contribution->objective_id = $objective->id;
                $contribution->amount = (int)$params[2];
                $contribution->contributor = \Crypt::encryptString($message->author->user->username." #".$message->author->user->discriminator);
                $contribution->save();

                $result = "Thank you. Your contribution has been logged.\n";
                $progress = $objective->contributions->sum('amount');
                if ($objective->target) {
                    $result .= "Progress is now ".number_format($progress)." / ".number_format($objective->target)."\n";
                    if ($objective->target <= $progress) {
                        $result .= "**Objective completed!**\n";
                    }
                } else {
                    $result .= "Progress is now ".number_format($progress)."\n";
                }
                return $this->safe($result);
            }
        }, [
            'description' => 'Record a contribution to a project objective.',
            'usage' => '<project code> <objective code> <amount>',
            'aliases' => ['contribution']
        ]);
    }
}
