<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\History;
use App\Models\Expansioncache;

class UpdateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:history {--tick=0} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the history table';

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
            $this->updateHistory();
            $this->updateExpansionCache();
        });
        //
    }

    private function updateHistory() {
        $debug = $this->option('debug');
            
        if ($tick = $this->option('tick')) {
            $tick = new Carbon($tick);
        } else {
            $tick = \App\Util::tick();
        }
        $previous = $tick->copy()->subDay();
            
        History::where('date', $tick->format("Y-m-d 00:00:00"))
            ->where('location_type', 'App\Models\System')
            ->whereIn('description', ['expanded to', 'retreated from'])
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
                    $history->description = 'expanded to';
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

        $invdist = 22.5;
        
        foreach (System::where('population', '>', 0)->get() as $system) {
            list($pts, $ats) = $system->expansionsFor(null); // null = controlling
            $found = 0;
            $atsp = 0;
            for ($i=0;$i<=2;$i++) {
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
                    ($pts[$i]->distanceTo($system) <= $invdist) ||
                    (isset($ats[$atsp]) && $pts[$i]->distanceTo($system) <= $ats[$atsp]->distanceTo($system)) ||
                    !isset($ats[$atsp])
                )) {
                    $found++;
                    $ec = new ExpansionCache;
                    $ec->system_id = $system->id;
                    $ec->target_id = $pts[$i]->id;
                    $ec->priority = $found;
                    $ec->hostile = false;
                    $ec->save();
                } else if (isset($ats[$atsp])) {
                    $found++;
                    $ec = new ExpansionCache;
                    $ec->system_id = $system->id;
                    $ec->target_id = $ats[$atsp]->id;
                    $ec->priority = $found;
                    $ec->hostile = true;
                    $ec->save();
                    $atsp++;
                    $i--;
                }
                if ($found >= 3) {
                    break;
                }
            }
        }
    }

    
    private function map($infs) {
        $factions = [];
        foreach ($infs as $inf) {
            $factions[] = $inf->faction;
        }
        return collect($factions);
    }
}
