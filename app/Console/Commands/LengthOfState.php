<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\System;
use App\Models\State;
use App\Models\Influence;

class LengthOfState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:lengthofstate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse maximum state lengths';

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
        $states = State::get();
        $lengths = [];
        $details = [];
        $names = [];
        foreach ($states as $state) {
            $names[$state->id] = $state->name;
            $lengths[$state->id] = 0;
            $details[$state->id] = "";
        }

        $factions = Faction::notHidden()->notVirtual()->get();
        $systems = System::populated()->get();

        foreach ($factions as $faction) {
            foreach ($systems as $system) {
                $influences = Influence::where('faction_id', $faction->id)->where('system_id', $system->id)->orderBy('date')->get();
                $current = 0;
                $start = 0;
                $last = 0;
                $lastsid = 0;
                foreach ($influences as $influence) {
                    $sid = $influence->state_id;
//                    $this->line($influence->date->format("Ymd")." ".$names[$sid]);
                    if ($sid != $current) {
                        if ($current != 0) {
                            $length = $last->diffInDays($start);
                            if ($length > $lengths[$lastsid]) {
                                $lengths[$lastsid] = $length;
                                $details[$lastsid] = $faction->name." ".$system->name." ".$start->format("Ymd");
                                $this->line($names[$lastsid]." = ".$length." (".$details[$lastsid].")");
                            }
                        }
                        $start = $last = $influence->date;
                        $current = $lastsid = $sid;
                    } else {
                        if ($influence->date->diffInDays($last) > 5) {
                            // assume a retreat and re-expansion
                            // reset the count
                            $start = $last = $influence->date;
                            $current = $lastsid = $sid;
                        } else {
                            $last = $influence->date;
                        }
                    }
                }
            }
        }

        foreach ($states as $state) {
            $sid = $state->id;
            $this->info($names[$sid]." = ".$lengths[$sid]." (".$details[$sid].")");
        }
    }
    
}
