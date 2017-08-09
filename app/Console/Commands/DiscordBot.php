<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\Faction;
use App\Models\Station;
use App\Models\State;
use App\Models\Influence;

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

        $this->registerStatusCommand();
        $this->registerSystemCommand();
        $this->registerStationCommand();
        $this->registerFactionCommand();
        $this->registerInfluenceCommand();
        $this->registerReportCommand();

        $this->discord->on('ready', function() {
            $game = $this->discord->factory(\Discord\Parts\User\Game::class, [
                'name' => route('index'),
            ]);
            
            $this->discord->updatePresence($game);
        });
        
        $this->discord->run();

    }

    private function registerStatusCommand() {
        $this->discord->registerCommand('status', function($message, $params) {
            return "Responding to commands.";
        }, [
            'description' => 'Give bot status'
        ]);
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
                return $result;
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
                return $result;
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
                return $result;
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
                return $sname." not known";
            } else {
                if ($date !== null) {
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($date)."**\n";
                    $result .= "<".route('systems.showhistory', $system->id).">\n";

                    $influences = $system->factions($date);
                    if ($influences->count() == 0) {
                        $result .= "No data for this date";
                        return $result;
                    }
                } else {
                    $influences = $system->latestFactions();
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($influences[0]->date)."**\n";
                    $result .= "<".route('systems.showhistory', $system->id).">\n";
                }
                foreach ($influences as $influence) {
                    $result .= $influence->faction->name.": ".$influence->influence."%, ".$influence->state->name."\n";
                }
                return $result;
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
                        return $result;
                    }
                } else {
                    $report = $system->latestReport();
                    $result = "**".$system->displayName()."** on **".\App\Util::displayDate($report->date)."**\n";
                    $result .= "<".route('systems.show', $system->id)."#reporthistory>\n";
                }
                $result .= "**Traffic**: ".number_format($report->traffic)."\n";
                $result .= "**Crimes**: ".number_format($report->crime)."\n";
                $result .= "**Bounties**: ".number_format($report->bounties)."\n";
                return $result;
            }
        }, [
            'description' => 'Return activity reports for a system. If the date is omitted will give the latest report.',
            'usage' => '[yyyy-mm-dd?] <system name>',
        ]);
    }
}
