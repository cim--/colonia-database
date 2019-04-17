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
        try {
        $states = State::get();
        $lengths = [];
        $details = [];
        $names = [];
        foreach ($states as $ostate) {
            $names[$ostate->id] = $ostate->name;
            $lengths[$ostate->id] = 0;
            $details[$ostate->id] = "";
        }

        $factions = Faction::notHidden()->notVirtual()->get();
        $systems = System::populated()->get();

        foreach ($states as $ostate) {
            $state = $ostate->id;
            foreach ($factions as $faction) {
                foreach ($systems as $system) {
                    $influences = Influence::where('faction_id', $faction->id)->where('system_id', $system->id)->where('date', '>=', '2019-01-15')->orderBy('date')->with('states')->get();
                    $current = 0;
                    $start = 0;
                    $last = 0;
                    $lastsid = 0;
                    $contains = false;
                    foreach ($influences as $influence) {
                        $contains = $influence->states->pluck('id')->contains($state);
                        if (!$contains) {
                            if ($current != 0) {
                                $length = $last->diffInDays($start);
                                if ($length > $lengths[$state]) {
                                    $lengths[$state] = $length;
                                    $details[$state] = $faction->name." ".$system->name." ".$start->format("Ymd");
                                    $this->line($names[$state]." = ".$length." (".$details[$state].")");
                                }
                                $current = 0;
                            }
                            $start = $last = $influence->date;
                        } else {
                            if ($last === 0 || $influence->date->diffInDays($last) > 5) {
                                // assume a retreat and re-expansion
                                // reset the count
                                $start = $last = $influence->date;
                                $current = 1;
                            } else {
                                $last = $influence->date;
                                $current = 1;
                            }
                        }
                    }
                    if ($contains) {
                        $length = $last->diffInDays($start);
                        if ($length > $lengths[$state]) {
                            $lengths[$state] = $length;
                            $details[$state] = $faction->name." ".$system->name." ".$start->format("Ymd");
                            $this->line($names[$state]." = ".$length."+ (".$details[$state].")");
                        }
                    }
                }
            }
        }
        
        foreach ($states as $state) {
            $sid = $state->id;
            $this->info($names[$sid]." = ".$lengths[$sid]." (".$details[$sid].")");
        }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }
    
}
