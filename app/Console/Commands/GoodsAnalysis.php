<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Station;
use App\Models\State;
use App\Models\Commodity;
use App\Models\Reserve;

class GoodsAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:goodsanalysis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $stations = Station::whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->with('economy')->get();

        $commodities = Commodity::all();
        
        foreach ($stations as $station) {
            foreach ($commodities as $commodity) {
                $this->analyse($station, $commodity);
            }
        }
    }

    public function analyse (Station $station, Commodity $commodity) {
        $reserves = Reserve::where('price', '!=', null)->where('station_id', $station->id)->where('commodity_id', $commodity->id)->with('state')->get();
        
        /* TODO: any history event affecting either the station or the
         * system it is in is likely to invalidate the comparison, so
         * should only track back to the most recent one. */

        $stockdata = [];
        $pricedata = [];
        $states = [];
        foreach ($reserves as $reserve) {
            if (!isset($stockdata[$reserve->state_id])) {
                $stockdata[$reserve->state_id] = [];
                $pricedata[$reserve->state_id] = [];
                $states[$reserve->state_id] = $reserve->state;
            }
            $stockdata[$reserve->state_id][] = $reserve->reserves;
            $pricedata[$reserve->state_id][] = $reserve->price;
        }

        if (count($stockdata) < 2) {
            // no state changes over comparison period
            return;
        }
        $this->info($station->name." - ".$station->economy->name." - ".$commodity->displayName());
        foreach ($stockdata as $sid => $rdata) {
            $stockavg = $this->mean($rdata);
            $priceavg = $this->mean($pricedata[$sid]);
            $this->line($states[$sid]->name." ".$stockavg." @ ".$priceavg." Cr.");
        }
        
    }

    private function mean($arr) {
        $acc = 0;
        foreach ($arr as $el) {
            $acc += $el;
        }
        return round($acc / count($arr));
    }
}
