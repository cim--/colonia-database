<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Alert;
use App\Models\System;
use App\Models\Faction;
use App\Models\History;
use App\Models\Expansioncache;
use App\Models\Eddnevent;
use App\Models\Influence;

class UpdateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:history {--tick=0} {--debug} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the history table and other caches';

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
        \DB::transaction(function() {
            if ($this->option('all')) {
                $this->updateAllHistory();
            } else {
                $this->updateHistory();
            }
            $this->updateExpansionCache();
            $this->clearEventData();
            $this->calculateRisks();
            $this->validateOwnerships();
        });
        //
    }

    
    private function updateAllHistory() {
        $start = new Carbon(Influence::min('date'));
        $now = \App\Util::tick();
        while ($start->lte($now)) {
            $this->line($start->format("Y-m-d"));
            $this->doUpdateHistory($start);
            $start->addDay();
        }
    }
    
    private function updateHistory() {
        if ($tick = $this->option('tick')) {
            $tick = new Carbon($tick);
        } else {
            $tick = \App\Util::tick();
        }
        $this->doUpdateHistory($tick);
    }

    private function doUpdateHistory($tick) {
        $debug = $this->option('debug');
        $previous = $tick->copy()->subDay();
            
        History::where('date', $tick->format("Y-m-d 00:00:00"))
            ->where('location_type', 'App\Models\System')
            ->whereIn('description', ['expanded to', 'retreated from', 'expanded by invasion to'])
            ->delete();

        $systems = System::where('population', '>', 0)->get();
        foreach ($systems as $system) {
            $getfactions = $system->factions($tick);
            $getlastfactions = $system->factionsGapproof($previous);
            if (count($getfactions) == 0 || count($getlastfactions) == 0) {
                continue; // no data
            }

            $factions = $this->map($getfactions);
            $lastfactions = $this->map($getlastfactions);
                

            foreach ($factions as $faction) {
                if (!$lastfactions->contains($faction)) {
                    if ($debug) {
                        $this->line($system->displayName().": expansion - ".$faction->name);
                    }
                    $history = new History;
                    $history->location_id = $system->id;
                    $history->location_type = 'App\Models\System';
                    $history->faction_id = $faction->id;
                    $history->date = $tick;
                    $history->expansion = true;
                    if ($factions->count() > 7) {
                        $history->description = 'expanded by invasion to';
                    } else {
                        $history->description = 'expanded to';
                    }
                    $history->save();
                }
            }
                
            foreach ($lastfactions as $faction) {
                if (!$factions->contains($faction)) {
                    if ($debug) {
                        $this->line($system->displayName().": retreat - ".$faction->name);
                    }
                    $history = new History;
                    $history->location_id = $system->id;
                    $history->location_type = 'App\Models\System';
                    $history->faction_id = $faction->id;
                    $history->date = $tick;
                    $history->expansion = false;
                    $history->description = 'retreated from';
                    $history->save();
                }
            }
        }
    }

    private function updateExpansionCache() {
        // needs a condition on it, so use an 'everything' one
        Expansioncache::where('priority', '>=', 0)->delete();

        $invdist = 20;
        
        foreach (System::where('population', '>', 0)->get() as $system) {
            $faction = $system->controllingFaction();
            if ($faction->virtual) {
                continue;
            }
            list($pts, $ats) = $system->expansionsFor($faction);
            $found = 0;
            $atsp = 0;
            for ($i=0;$i<=3;$i++) {
                /**
                 * If there's a peaceful expansion, and
                 * - it's less than the investment distance
                 * - or it's closer than the closest aggressive
                 * - or there isn't an aggressive
                 * then make it a candidate.
                 *
                 * Otherwise if there's an aggressive make that the
                 * candidate.
                 */
                if (isset($pts[$i]) && (
                    ($pts[$i]->expansionCube($system, $invdist)) ||
                    (isset($ats[$atsp]) && $pts[$i]->distanceTo($system) <= $ats[$atsp]->distanceTo($system)) ||
                    !isset($ats[$atsp])
                )) {
                    $found++;
                    $ec = new ExpansionCache;
                    $ec->system_id = $system->id;
                    $ec->target_id = $pts[$i]->id;
                    $ec->priority = $found;
                    $ec->hostile = false;
                    $ec->investment = !$pts[$i]->expansionCube($system, $invdist);
                    $ec->previousretreat = $faction->previouslyIn($pts[$i]);
                    $ec->save();
                } else if (isset($ats[$atsp])) {
                    $found++;
                    $ec = new ExpansionCache;
                    $ec->system_id = $system->id;
                    $ec->target_id = $ats[$atsp]->id;
                    $ec->priority = $found;
                    $ec->hostile = true;
                    $ec->investment = !$ats[$atsp]->expansionCube($system, $invdist);
                    $ec->previousretreat = $faction->previouslyIn($ats[$atsp]);
                    $ec->save();
                    $atsp++;
                    $i--;
                }
                if ($found >= 4) {
                    break;
                }
            }
        }
    }

    private function clearEventData() {
        /* These are only used for counting onto travel reports (and
         * in future generating estimated travel reports). In theory
         * we should only need the last 24 hours, but keep slightly
         * longer just in case. */
        Eddnevent::where('eventtime', '<', date("Y-m-d H:i:s", strtotime("-7 days")))->delete();
    }
    
    private function map($infs) {
        $factions = [];
        foreach ($infs as $inf) {
            $factions[] = $inf->faction;
        }
        return collect($factions);
    }

    private function calculateRisks()
    {
        $debug = $this->option('debug');

        $systems = System::where('bgslock', 0)->where('population', '>', 0)->get();
        foreach ($systems as $system) {
            $risk = 0;
            $is = $system->latestFactionsWithoutEagerLoad();
            if ($is->count() > 1) {
                $diff = $is[0]->influence - $is[1]->influence;
                if ($diff == 0 || $system->controllingFaction()->id != $is[0]->faction_id) {
                    $risk = 5; // control conflict or controller not first
                } else if ($diff < 40) {
                    $expansion = 0;
                    if ($is[0]->states()->where('name', 'Expansion')->count() == 0) {
                        $expansion = 1;
                        if ($debug) {
                            $this->line("No expansion in ".$system->name);
                        }
                    }
                    $date = $is[0]->date->copy();
                    $tmax = 0;
                    $ldiff = $diff;
                    for ($t=1;$t<=5;$t++) {
                        $date->subDay();
                        $prev = $system->factionsWithoutEagerLoad($date);
                        if (isset($prev[0]) && isset($prev[1])) {
                            $pdiff = $prev[0]->influence - $prev[1]->influence;
                            if ($expansion == 1) {
                                if ($prev[0]->states()->where('name', 'Expansion')->count() > 0) {
                                    $expansion = 2;
                                    if ($debug) {
                                        $this->line("Expansion previously in ".$system->name);
                                    }
                                    // expansion occurred, tax paid
                                }
                            }

                            // if expansion = 2, more complex pdiff
                            if (($expansion == 2 && $pdiff > 10+$ldiff) || $expansion == 3) {
                                // likely has expansion contribution
                                $pdiff -= 10; // conservative estimate
                                $expansion = 3;
                                if ($debug) {
                                    $this->line("-> Accounting for expansion tax");
                                }
                                // don't want to miss changes stacking with it
                            }

                            /* Difference in the difference N days ago
                             * and the difference today, divided by N,
                             * is the rate of closure per day */
                            $trend = ($pdiff-$diff)/$t;
                            if ($trend > $tmax) {
                                $tmax = $trend;
                            }
                            $ldiff = $pdiff;
                        }
                    }
                    if ($tmax > 0) {
                        $margin = $diff / $tmax;
                        
                        if ($margin < 5) {
                            $risk = floor(5 - $margin);
                        }
                    }
                }
            }
            $system->risk = $risk;
            $system->save();
        }
    }

    private function validateOwnerships()
    {
        // ignore non-influence factions
        $specials = Faction::where('virtual', 1)->pluck('id');
        
        foreach (System::all() as $system) {
            $factions = $system->latestFactions()->pluck('faction_id');
            $factionids = $factions->merge($specials);

            $controller = $system->controllingFaction();
            
            foreach ($system->stations as $station) {
                if (!$factionids->contains($station->faction_id)) {
                    Alert::alert("Owner of ".$station->name." in ".$system->displayName()." is not present");
                    if ($controller->id != $station->faction_id) {
                        // assume a retreat and reassign to controller
                        $station->changeOwnership($controller);
                    }
                }
            }

            foreach ($system->installations as $installation) {
                if (!$factionids->contains($installation->faction_id)) {
                    Alert::alert("Owner of ".$installation->displayName()." in ".$system->displayName()." is not present");
                    if ($controller->id != $installation->faction_id) {
                        // assume a retreat and reassign to controller
                        $installation->changeOwnership($controller);
                    }
                }
            }
            
        }
    }
}
