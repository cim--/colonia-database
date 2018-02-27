<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Station;
use App\Models\State;
use App\Models\Commodity;
use App\Models\Reserve;
use App\Models\History;
use App\Models\Effect;
use App\Models\Tradebalance;
use App\Models\Economy;

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

    protected $commodityinfo = [];
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::transaction(function() {
                $this->runGoodsAnalysis();
                $this->runBalanceAnalysis();
            });
        } catch (\Throwable $e) {
            print($e->getTraceAsString());
            throw($e);
        }
    }

    private function runGoodsAnalysis() {
        Effect::where('id', '>', 0)->delete();
        
        $stations = Station::whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('economy', function($q) {
            // ignore stations with hybrid or damaged economies
            // as they'll confuse the analysis
            $q->where('analyse', true);
        })->with('economy')->get();

        $commodities = Commodity::all();

        foreach ($commodities as $commodity) {
            $this->commodityinfo[$commodity->id] = [
                'commodity' => $commodity,
                'statechanges' => []
            ];
            foreach ($stations as $station) {
                $this->analyseReserves($station, $commodity);
            }
            if (count($this->commodityinfo[$commodity->id]['statechanges']) > 0) {
                $this->analyseStates($commodity);
            }
        }
    }

    public function analyseReserves(Station $station, Commodity $commodity) {
        $laststationhistory = History::where('location_type', 'App\Models\Station')
            ->where('location_id', $station->id)->max('date');
        $lastsystemhistory = History::where('location_type', 'App\Models\System')
            ->where('location_id', $station->system->id)
            ->where('description', '!=', 'expanded to')
            ->where('description', '!=', 'retreated from')->max('date');
        
        $reservesquery = Reserve::where('price', '!=', null)
            ->where('station_id', $station->id)
            ->where('commodity_id', $commodity->id)
            ->where('reserves', '!=', 0)->with('state');
        if ($laststationhistory != null) {
            $reservesquery->where('date', '>', $laststationhistory);
        }
        if ($lastsystemhistory != null) {
            $reservesquery->where('date', '>', $lastsystemhistory);
        }
        $reserves = $reservesquery->get();
        
        /* Any history event affecting either the station or the
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
        if (count($stockdata) > 0) {
            // look for states with no demand or supply
            $stateslist = State::whereHas('reserves', function($q) use ($laststationhistory, $lastsystemhistory, $station) {
                if ($laststationhistory != null) {
                    $q->where('date', '>', $laststationhistory);
                }
                if ($lastsystemhistory != null) {
                    $q->where('date', '>', $lastsystemhistory);
                }
                $q->where('station_id', $station->id);
            })->where('name', '!=', 'None')->get();
            /* Putting zeroes in for None can have odd effects, so don't */
            foreach ($stateslist as $state) {
                if (!isset($stockdata[$state->id])) {
                    $stockdata[$state->id] = [0];
                    $pricedata[$state->id] = [0];
                    $states[$state->id] = $state;
                }
            }
        }

        if (count($stockdata) < 2) {
            // no state changes over comparison period
            return;
        }
////        $this->info($station->name." - ".$station->economy->name." - ".$commodity->displayName());
        $entries = [];
        ksort($stockdata); // always process in same order
        foreach ($stockdata as $sid => $rdata) {
            $stockavg = $this->mean($rdata);
            $priceavg = $this->mean($pricedata[$sid]);
////            $this->line($sid." ".$states[$sid]->name." ".$stockavg." @ ".$priceavg." Cr. (".count($rdata)." samples).");
            $entries[] = [
                'state' => $states[$sid],
                'stock' => $stockavg,
                'price' => $priceavg
            ];
        }
        $this->commodityinfo[$commodity->id]['statechanges'][] = $entries;
    }

    private function mean($arr) {
        $acc = 0;
        $zeroes = 0;
        foreach ($arr as $el) {
            $acc += $el;
            if ($el == 0) {
                $zeroes++;
            }
        }
        if ($zeroes == count($arr)) {
            // don't include zeroes in average unless the entire array is zeroed
            return 0;
        }
        return $acc / (count($arr) - $zeroes);
    }


    private function analyseStates(Commodity $commodity) {
        $states = State::all();
        $statedata = [];
        foreach ($states as $state) {
            $statedata[$state->id] = [
                'state' => $state,
                'pricefactor' => [],
                'demandfactor' => [],
                'supplyfactor' => []
            ];
        }

        $supplyset = false;
        $demandset = false;

        usort($this->commodityinfo[$commodity->id]['statechanges'], function($a, $b) {
            return count($b)-count($a);
        });
        
        foreach ($this->commodityinfo[$commodity->id]['statechanges'] as $idx => $stationinfo) {

            $first = $stationinfo[0];

            if (($first['stock'] > 0 && !$supplyset) ||
            ($first['stock'] < 0 && !$demandset)) {
                // use the first known state to set a baseline
                if ($first['stock'] > 0) {
                    $statedata[$first['state']->id]['supplypricefactor'] = [1];
                    $statedata[$first['state']->id]['supplyfactor'] = [1];
                    $supplyset = true;
                } else {
                    $statedata[$first['state']->id]['demandpricefactor'] = [1];
                    $statedata[$first['state']->id]['demandfactor'] = [1];
                    $demandset = true;
                }
                // then build from that baseline
                for ($i=1;$i<count($stationinfo);$i++) {
                    $second = $stationinfo[$i];
                    if ($first['stock'] > 0) {
                        $statedata[$second['state']->id]['supplyfactor'][] = $second['stock'] / $first['stock'];
                        $statedata[$second['state']->id]['supplypricefactor'][] = $second['price'] / $first['price'];
                    } else {
                        $statedata[$second['state']->id]['demandfactor'][] = $second['stock'] / $first['stock'];
                        $statedata[$second['state']->id]['demandpricefactor'][] = $second['price'] / $first['price'];
                    }
                }
            } else {
                // attach items to the baseline if possible
                for ($i=0;$i<count($stationinfo);$i++) {
                    $first = $stationinfo[$i];

                    // find one which is already on the baseline
                    if (($first['stock'] > 0 && count($statedata[$first['state']->id]['supplyfactor']) > 0) ||
                    ($first['stock'] < 0 && count($statedata[$first['state']->id]['demandfactor']) > 0)) {
                        for ($j=0;$j<count($stationinfo);$j++) {
                            if ($j != $i) {
                                $second = $stationinfo[$j];
                                if ($first['stock'] > 0) {
                                    $statedata[$second['state']->id]['supplyfactor'][] = $this->mean($statedata[$first['state']->id]['supplyfactor']) * $second['stock'] / $first['stock'];
                                    $statedata[$second['state']->id]['supplypricefactor'][] = $this->mean($statedata[$first['state']->id]['supplypricefactor']) * $second['price'] / $first['price'];
                                } else {
                                    $statedata[$second['state']->id]['demandfactor'][] = $this->mean($statedata[$first['state']->id]['demandfactor']) * $second['stock'] / $first['stock'];
                                    $statedata[$second['state']->id]['demandpricefactor'][] = $this->mean($statedata[$first['state']->id]['demandpricefactor']) * $second['price'] / $first['price'];
                                }
                        
                            }
                        }
                        break; // only need to do one
                        // if none match to the existing baseline we
                        // can't use it, but we put the biggest arrays
                        // first so shouldn't lose too much
                    }
                }

            }
        }

////        $this->info("Commodity: ".$commodity->displayName());
        foreach ($statedata as $stateinfo) {
            if (count($stateinfo['supplyfactor']) > 0 || count($stateinfo['demandfactor']) > 0) {
////                $this->info("  State: ".$stateinfo['state']->name);
                $effect = new Effect;
                $effect->commodity_id = $commodity->id;
                $effect->state_id = $stateinfo['state']->id;
                if (count($stateinfo['supplyfactor']) > 0) {
////                    $this->line("    Exports: ".number_format($this->mean($stateinfo['supplyfactor']),2)."x @ ".number_format($this->mean($stateinfo['supplypricefactor']),2)."x Cr");
                    $effect->supplysize = $this->mean($stateinfo['supplyfactor']);
                    $effect->supplyprice = $this->mean($stateinfo['supplypricefactor']);
                }
                if (count($stateinfo['demandfactor']) > 0) {
////                    $this->line("    Imports: ".number_format($this->mean($stateinfo['demandfactor']),2)."x @ ".number_format($this->mean($stateinfo['demandpricefactor']),2)."x Cr");
                    $effect->demandsize = $this->mean($stateinfo['demandfactor']);
                    $effect->demandprice = $this->mean($stateinfo['demandpricefactor']);
                }
                $effect->save();
            }
        }

    }


    private function runBalanceAnalysis() {
        $economies = Economy::where('analyse', true)->get();
        $states = State::where('name', '!=', 'Lockdown')->get();

        foreach ($economies as $economy) {
            foreach ($states as $state) {
                $balance = Tradebalance::firstOrNew([
                    'economy_id' => $economy->id,
                    'state_id' => $state->id,
                ]);
                $tr = $economy->tradeRatio($state);
                $tpr = $economy->tradePriceRatio($state);
                if ($tr !== null) {
                    $balance->volumebalance = 100*$tr;
                    $balance->creditbalance = 100*$tpr;
                } else {
                    $balance->volumebalance = null;
                    $balance->creditbalance = null;
                }
                $balance->save();
            }
        }
    }
}
