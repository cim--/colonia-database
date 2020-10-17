<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Commodity;
use App\Models\Reserve;
use App\Models\Station;

class TradeCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:tradecleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up unnecessary trade reserves information';

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
        // get all because they might have had commodity markets
        $stations = Station::all();
        $commodities = Commodity::all();
        //        $stations = Station::where('id', 1)->get();
        //        $commodities = Commodity::where('id', 1)->get();
        foreach ($stations as $station) {
            $this->info($station->name);
            foreach ($commodities as $commodity) {
                $this->info($commodity->name);
                $this->compactReserves($station, $commodity);
            }
        }
        $this->cleanOutfitting();

        // compact tables
        \DB::query("OPTIMIZE TABLE reserves");
        \DB::query("OPTIMIZE TABLE reserve_state");
    }

    private function compactReserves(Station $station, Commodity $commodity)
    {
        \DB::transaction(function() use ($station, $commodity) {

            // keep full data for current month
            $threshold = date("Y-m-01");
            $reserves = Reserve::where('station_id', $station->id)->where('commodity_id', $commodity->id)->where('created_at', '<', $threshold)->with('states')->orderBy('created_at')->cursor();
            $last = null;
            $curr = null;
            $next = null;
            $del = 0;
            foreach ($reserves as $reserve) {
                if ($last == null) {
                    $last = $reserve;
                    continue;
                } else if ($curr == null) {
                    $curr = $reserve;
                    continue;
                } else if ($next == null) {
                    $next = $reserve;
                } else {
                    // slide
                    $last = $curr;
                    $curr = $next;
                    $next = $reserve;
                }
//            $this->line([$last->reserves, $curr->reserves, $next->reserves]);
                if ($last->reserves == $curr->reserves && $curr->reserves == $next->reserves && $last->stateString() == $curr->stateString() && $curr->stateString() == $next->stateString() && $last->price == $curr->price && $curr->price == $next->price) {
                    // all three are materially the same
                    // delete the middle one
                    $curr->states()->detach(); //clean up reserve_state
                    $curr->delete();
                    $del++;
                    // and slide the others up
                    $curr = $next;
                    $next = null;
                } 
            }
            $this->line("Deleted ".$del." / ".$reserves->count());
        });

    }

    private function cleanOutfitting() {
        // remove outfitting entries over 3 months old, as probably
        // not actually on production list (and may have been data glitches).
        \DB::delete("DELETE FROM module_station WHERE current = 0 AND updated_at < '".Carbon::parse("-3 months")->format("Y-m-d")."'");
    }
}
