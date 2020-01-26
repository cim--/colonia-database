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
    protected $signature = 'cdb:goodsanalysis {--balanceonly} {--statesonly} {--testmode} {--singlepass}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse goods to find state effects';

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
    protected $calculated = [];
    protected $statenone = 0;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->statenone = State::where('name', 'None')->first()->id;
        try {
            if (!$this->option('balanceonly')) {
                $this->runGoodsAnalysis();
            } else {
                $this->info("Balance analysis only");
            }
            \DB::transaction(function() {
                if (!$this->option('statesonly')) {
                    $this->runBalanceAnalysis();
                } else {
                    $this->info("State analysis only");
                }
            });
        } catch (\Throwable $e) {
            print($e->getTraceAsString());
            throw($e);
        }
    }

    private function runGoodsAnalysis() {
        $stations = Station::whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('economy', function($q) {
            // ignore stations with hybrid or damaged economies
            // as they'll confuse the analysis
            $q->where('analyse', true);
        })->with('economy')->get();

        if ($this->option('testmode')) {
            $commodities = Commodity::where('id', 23)->get();
        } else {
            $commodities = Commodity::all();
        }

        foreach ($commodities as $idx => $commodity) {

            \DB::transaction(function() use ($commodity, $stations, $idx) {
                Effect::where('commodity_id', $commodity->id)->delete();
                $this->info("Commodity: ".$commodity->name);
                $this->commodityinfo[$commodity->id] = [
                    'commodity' => $commodity,
                    'statechanges' => []
                ];
                foreach ($stations as $station) {
                    $this->line("Station, single mode: ".$station->name);
                    $this->analyseReserves($station, $commodity);
                }
                $improved = false;
                if (count($this->commodityinfo[$commodity->id]['statechanges']) > 0) {
                    $this->info("Analysing states, pass 1");
                    $improved = $this->analyseStates($commodity, 1);
                }
                $pass = 1;
                if ($improved && !$this->option('singlepass')) {
                    do {
                        $improved = false;
                        $pass++;
                        // reset data
                        $this->commodityinfo[$commodity->id]['statechanges'] = [];
                        foreach ($stations as $station) {
                            $this->line("Station, multiple mode: ".$station->name);
                            $this->analyseReserves2($station, $commodity);
                        }
                        if (count($this->commodityinfo[$commodity->id]['statechanges']) > 0) {
                            $this->info("Analysing states, pass ".$pass);
                            $improved = $this->analyseStates($commodity, $pass);
                        }
                    } while ($improved);
                }
            });
        }
    }

    public function getReserves(Station $station, Commodity $commodity) {
        $laststationhistory = History::where('location_type', 'App\Models\Station')
            ->where('location_id', $station->id)->max('date');
        $lastsystemhistory = History::where('location_type', 'App\Models\System')
            ->where('location_id', $station->system->id)
            ->where('description', '!=', 'expanded to')
            ->where('description', '!=', 'expanded by invasion to')
            ->where('description', '!=', 'retreated from')->max('date');
        /* Any history event affecting either the station or the
         * system it is in is likely to invalidate the comparison, so
         * should only track back to the most recent one. */

        $reservesquery = Reserve::where('price', '!=', null)
            ->where('station_id', $station->id)
            ->where('commodity_id', $commodity->id)
            ->where('reserves', '!=', 0)->with('states');
        if ($laststationhistory != null) {
            $reservesquery->where('date', '>', $laststationhistory);
        }
        if ($lastsystemhistory != null) {
            $reservesquery->where('date', '>', $lastsystemhistory);
        }
        // significant changes to some goods in 3.0, so don't look before
        $reservesquery->normalMarkets();
        
        $reserves = $reservesquery->get();
        return $reserves;
    }

    /* The first pass will check for single-state market entries only. */
    public function analyseReserves(Station $station, Commodity $commodity) {
        $reserves = $this->getReserves($station, $commodity);

        $stockdata = [];
        $pricedata = [];
        $states = [];
        foreach ($reserves as $reserve) {
            if ($reserve->states->count() > 1) {
                continue; // do the easy ones first
            }
            $stateid = $reserve->states[0]->id;

            if (!isset($stockdata[$stateid])) {
                $stockdata[$stateid] = [];
                $pricedata[$stateid] = [];
                $states[$stateid] = $reserve->states[0];
            }
            $stockdata[$stateid][] = $reserve->reserves;
            $pricedata[$stateid][] = $reserve->price;
        }

        if (count($stockdata) < 2) {
            // no state changes over comparison period
            return;
        }
////        $this->info($station->name." - ".$station->economy->name." - ".$commodity->displayName());
        $entries = [];
        ksort($stockdata); // always process in same order
        foreach ($stockdata as $sid => $rdata) {
            $stockavg = $this->median($rdata);
            $priceavg = $this->median($pricedata[$sid]);
////            $this->line($sid." ".$states[$sid]->name." ".$stockavg." @ ".$priceavg." Cr. (".count($rdata)." samples).");
            $entries[] = [
                'state' => $states[$sid],
                'stock' => $stockavg,
                'price' => $priceavg
            ];
        }
        $this->commodityinfo[$commodity->id]['statechanges'][] = $entries;
    }

    public function reduceStates(Reserve $reserve) {
        $states = $reserve->states;

        // supply demand price tonnes factors
        $sp = 1; $st = 1; $dp = 1; $dt = 1;

        $unknowns = [];

        $supply = ($reserve->reserve > 0);
        // true/false
        
        foreach ($states as $state) {
            if (isset($this->calculated[$reserve->commodity_id][$state->id])) {
                $effect = $this->calculated[$reserve->commodity_id][$state->id];
                if ($supply) {
                    if ($effect->supplysize !== null) {
                        $st *= $effect->supplysize;
                        $sp *= $effect->supplyprice;
                        continue;
                    }
                } else {
                    if ($effect->demandsize !== null) {
                        $st *= $effect->demandsize;
                        $sp *= $effect->demandprice;
                        continue;
                    }
                }
            }
            // if haven't already continued, then there's no valid
            // effect for this state
            $unknowns[] = $state;
        }

        if (count($unknowns) > 2) {
            // can't use this
            return null;
        }
        // adjust the reserve to be what it would have been with just
        // this state
        if ($supply) {
            if ($st == 0) {
                return null; // can't use this one
            }
            $reserve->reserve = round($reserve->reserve / $st);
            $reserve->price = round($reserve->price / $sp);
        } else {
            if ($dt == 0) {
                return null; // can't use this one
            }
            $reserve->reserve = round($reserve->reserve / $dt);
            $reserve->price = round($reserve->price / $dp);
        }
        if (count($unknowns) == 1) {
            // reduces to this state
            return $unknowns[0]->id;
        }
        // else reduces to 'none'
        return $this->statenone;
    }
    
    /* The second pass will use the first pass to try to reduce
     * multi-state entries to single-state ones */
    public function analyseReserves2(Station $station, Commodity $commodity) {
        $reserves = $this->getReserves($station, $commodity);

        $stockdata = [];
        $pricedata = [];
        $states = [];
        foreach ($reserves as $reserve) {
            if ($reserve->states->count() == 1) {
                $stateid = $reserve->states[0]->id;
            } else {
                // try to simplify
                $stateid = $this->reduceStates($reserve);
                if ($stateid === null) {
                    continue;
                }
            }
            // add more data
            if (!isset($stockdata[$stateid])) {
                $stockdata[$stateid] = [];
                $pricedata[$stateid] = [];
                $states[$stateid] = $reserve->states[0];
            }
            $stockdata[$stateid][] = $reserve->reserves;
            $pricedata[$stateid][] = $reserve->price;
        }

        if (count($stockdata) < 2) {
            // no state changes over comparison period
            return;
        }
////        $this->info($station->name." - ".$station->economy->name." - ".$commodity->displayName());
        $entries = [];
        ksort($stockdata); // always process in same order
        foreach ($stockdata as $sid => $rdata) {
            $stockavg = $this->median($rdata);
            $priceavg = $this->median($pricedata[$sid]);
////            $this->line($sid." ".$states[$sid]->name." ".$stockavg." @ ".$priceavg." Cr. (".count($rdata)." samples).");
            $entries[] = [
                'state' => $states[$sid],
                'stock' => $stockavg,
                'price' => $priceavg
            ];
        }
        $this->commodityinfo[$commodity->id]['statechanges'][] = $entries;
    }
    

    

    private function median($arr) {
        sort($arr);
        $mid = (count($arr)-1)/2;
        if ($mid == floor($mid)) {
            return $arr[$mid];
        } else {
            return ($arr[floor($mid)]+$arr[ceil($mid)])/2;
        }
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


    private function analyseStates(Commodity $commodity, $pass) {
        $improved = false;
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
                                    $statedata[$second['state']->id]['supplyfactor'][] = $this->median($statedata[$first['state']->id]['supplyfactor']) * $second['stock'] / $first['stock'];
                                    $statedata[$second['state']->id]['supplypricefactor'][] = $this->median($statedata[$first['state']->id]['supplypricefactor']) * $second['price'] / $first['price'];
                                } else {
                                    $statedata[$second['state']->id]['demandfactor'][] = $this->median($statedata[$first['state']->id]['demandfactor']) * $second['stock'] / $first['stock'];
                                    $statedata[$second['state']->id]['demandpricefactor'][] = $this->median($statedata[$first['state']->id]['demandpricefactor']) * $second['price'] / $first['price'];
                                }
                        
                            }
                        }
                        break; // only need to do one
                        // if none match to the existing baseline we
                        // can't use it, but we put the biggest arrays
                        // first so shouldn't lose too much and might get them
                        // on the next pass anyway
                    }
                }

            }
        }

////        $this->info("Commodity: ".$commodity->displayName());
        foreach ($statedata as $stateinfo) {

            /* If this is the second pass, ignore any effects already
             * calculated on the first pass */

            if (count($stateinfo['supplyfactor']) > 0 || count($stateinfo['demandfactor']) > 0) {

                if (isset($this->calculated[$commodity->id]) && isset($this->calculated[$commodity->id][$stateinfo['state']->id])) {
                    /* If already calculated on earlier pass, just
                     * retrieve it */
                    $effect = $this->calculated[$commodity->id][$stateinfo['state']->id];
                } else {
                    $effect = new Effect;
                    $effect->commodity_id = $commodity->id;
                    $effect->state_id = $stateinfo['state']->id;
                }
                // don't recalculate if already known
                if (count($stateinfo['supplyfactor']) > 0 && $effect->supplysize === null) {
                    ////                    $this->line("    Exports: ".number_format($this->median($stateinfo['supplyfactor']),2)."x @ ".number_format($this->median($stateinfo['supplypricefactor']),2)."x Cr");
                    $effect->supplysize = $this->median($stateinfo['supplyfactor']);
                    $effect->supplyprice = $this->median($stateinfo['supplypricefactor']);
                    $improved = true;
                    $effect->spass = $pass;
                }
                if (count($stateinfo['demandfactor']) > 0 && $effect->demandsize === null) {
                    ////                    $this->line("    Imports: ".number_format($this->median($stateinfo['demandfactor']),2)."x @ ".number_format($this->median($stateinfo['demandpricefactor']),2)."x Cr");
                    $effect->demandsize = $this->median($stateinfo['demandfactor']);
                    $effect->demandprice = $this->median($stateinfo['demandpricefactor']);
                    $improved = true;
                    $effect->dpass = $pass;
                }
                $effect->save();
                if (!isset($this->calculated[$commodity->id])) {
                    $this->calculated[$commodity->id] = [];
                }
                // save for later
                $this->calculated[$commodity->id][$effect->state_id] = $effect;
                
            }
        }
        return $improved;
    }


    private function runBalanceAnalysis() {
        $economies = Economy::where('analyse', true)->get();
        $states = State::whereHas('reserves', function($q) {
            $q->normalMarkets();
        })->get();

        $this->info("Balance analysis stage");
        
        foreach ($economies as $economy) {
            $this->line($economy->name);
            foreach ($states as $state) {
                $this->line("... ".$state->name);
                $balance = Tradebalance::firstOrNew([
                    'economy_id' => $economy->id,
                    'state_id' => $state->id,
                ]);
                $tr = $economy->tradeRatio($state);
                $this->line("... ... tr = $tr");
                $tpr = $economy->tradePriceRatio($state);
                $this->line("... ... tpr = $tpr");
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
