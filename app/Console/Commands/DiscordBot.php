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
            'description' => "Colonia Census Information Retrieval",
            'prefix' => env('DISCORD_COMMAND_PREFIX', '!')
        ]);

        $this->registerStatusCommand();
        $this->registerSystemCommand();
        $this->registerStationCommand();
        $this->registerFactionCommand();

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
            'description' => 'Return information about the named system.'
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
            'description' => 'Return information about the named station.'
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
        });
    }
}
