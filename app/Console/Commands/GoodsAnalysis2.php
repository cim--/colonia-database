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
use App\Models\Baselinestock;
use App\Models\Commoditystat;

class GoodsAnalysis2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:goodsanalysis2 {--sizeonly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse goods to find restock rates';

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
    private $maxsupmultiplier = 0;
    private $maxdemmultiplier = 0;
    private $colonysizefactor = 0;
    private $genericsizefactor = 0;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        /* Derived by comparing hydrogen fuel baselines with CEI bases */
        $this->genericsizefactor = 1806.52032;
        $this->colonysizefactor = 0.18432;
        
        try {
            if (!$this->option('sizeonly')) {
                \DB::transaction(function() {
                    $this->runGoodsAnalysis();
                });
            } else {
                $this->info("Size analysis only");
            }
            \DB::transaction(function() {
                $this->runEconomySizeAnalysis();
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
            // ignore stations with damaged economies
            // as they'll confuse the analysis
            $q->baseline();
            // hybrids are fine for this, though
        })->with('economy')->get();

        $commodities = Commodity::with('effects')
            ->orderBy('name')->get();

        $states = State::orderBy('name')->get();

        Baselinestock::where('station_id','>',0)->delete();
        
        foreach ($commodities as $commodity) {
            $demandregen = [];
            $supplyregen = [];
            $this->info("Commodity: ".trim($commodity->description));
            foreach ($stations as $station) {
                $this->maxsupmultiplier = 0;
                $this->maxdemmultiplier = 0;
                foreach ($states as $state) {
                    $regen = $this->analyseRegeneration($commodity, $station, $state);
                    if ($regen !== null) {
                        if ($regen > 0) {
                            $supplyregen[] = $regen;
                        } else if ($regen < 0) {
                            $demandregen[] = $regen;
                        }
                    }
                }
            }
            $estimate = "Neither";
            if (count($supplyregen)>0) {
                $commodity->supplycycle = floor($this->median($supplyregen));
            } else if ($commodity->averageprice > 0) {
                /* The broad trend seems to be 2 days + 1 minute per
                 * credit of average price */
                $commodity->supplycycle = 172800 + (60 * $commodity->averageprice);
                $estimate = "Supply";
            } else {
                $commodity->supplycycle = null;
            }
            if (count($demandregen)>0) {
                $commodity->demandcycle = floor($this->median($demandregen));
            } else if ($commodity->averageprice > 0) {
                /* The broad trend seems to be 2.5x the supply cycle,
                 * so 5 days + 2.5 minutes per credit of average
                 * price */
                $commodity->demandcycle = -(432000 + (150 * $commodity->averageprice));
                $estimate = ($estimate == "Supply") ? "Both" : "Demand";
            } else {
                $commodity->demandcycle = null;
            }
            $commodity->cycleestimate = $estimate;
            $commodity->save();
        }
        
    }

    public function analyseRegeneration(Commodity $commodity, Station $station, State $state) {
        
        $reservesquery = Reserve::where('price', '!=', null)
            ->where('station_id', $station->id)
            ->where('commodity_id', $commodity->id)
            ->whereHas('states', function ($q) use ($state) {
                $q->where('states.id', $state->id);
            })
            ->withCount('states')
            ->where('reserves', '!=', 0)
            ->normalMarkets();

        /* Where stations change economy, only look after the change */
        if ($station->id == 1 || $station->id == 2) {
            // Jaques, Hub conversion
            $reservesquery->where('date', '>', '2019-06-12');
        } else if ($station->id == 22) {
            // Kremmens conversion
            $reservesquery->where('date', '>', '2019-03-29');
        }
        
        $reserves = $reservesquery->get();

        if ($reserves->count() == 0) {
            return null;
        }

        $effect = Effect::where('commodity_id', $commodity->id)
            ->where('state_id', $state->id)->first();
        
        $sign = ($reserves[0]->reserves > 0)?1:-1;

        $max = 0;
        $stability = 0;

        $newmax = 0;
        $newstab = 0;
        foreach ($reserves as $reserve) {
            if ($reserve->states_count != 1) {
                // this is going to be messy if we use mixed states
                // see if we have enough data to get it to work without
                continue;
            }
            if (abs($reserve->reserves) > $max) {
                if ($stability > 5) {
                    /* It's possible that someone sold back to a
                     * max-supply market */
                    if (abs($reserve->reserves) != $newmax) {
                        $newmax = abs($reserve->reserves);
                        $newstab = 1;
                    } else {
                        $newstab++;
                        if ($newstab >= 5) {
                            // now there's been at least as many, so use it
                            $newmax = 0;
                            $newstab = 0;
                            $max = abs($reserve->reserves);
                            $stability = 1;
                        }
                    }
                } else {
                    $max = abs($reserve->reserves);
                    $stability = 1;
                }
            } else if (abs($reserve->reserves) == $max) {
                $stability++;
            }
        }
        if (($stability < 5 && $commodity->id != 1) || $max == 0) {
            // insufficient to confirm baseline
            // will use for HFuel *anyway* because a guess is better than nothing
            return null;
        }

        if ($effect) {
            $multiplier = ($sign==1)?$effect->supplysize:$effect->demandsize;

            /* Really high multipliers can give odd results as they
             * push the level over the 999999 display limit. */
            if ($multiplier >= 1 && $multiplier > ($sign==1?$this->maxsupmultiplier:$this->maxdemmultiplier) && $multiplier < 10) {
                if ($sign == 1) {
                    $this->maxsupmultiplier = $multiplier;
                } else {
                    $this->maxdemmultiplier = $multiplier;
                }
                /* Because of discrepancies between estimated tick,
                 * commodity tick and system state tick, it's best to use
                 * the highest multiplier state and then divide back down
                 * for the estimates of the baseline maximum, as 'max' for
                 * this is most likely to be accurate */
            
                Baselinestock::where('station_id', $station->id)
                    ->where('commodity_id', $commodity->id)->delete();
            
                $baseline = new Baselinestock;
                $baseline->station_id = $station->id;
                $baseline->commodity_id = $commodity->id;
                $baseline->reserves = ($max*$sign) / $multiplier;
                $baseline->save();
            }
        }
        
        if ($stability < 25) {
            // unlikely to be good enough for slope estimation
            return null;
        }

        
        $slopes = [];
        $last = null;
        foreach ($reserves as $reserve) {
            $amount = abs($reserve->reserves);
            if ($last !== null) {
                $lastamount = abs($last->reserves);
            }
            if ($amount == $max) {
                $last = null; // can't use
            } else if ($last == null) {
                $last = $reserve; // start again
            } else if ($amount < $lastamount) {
                $last = $reserve; // can't use, try again
            } else if ($amount == $lastamount) {
                // no change since last check
                // don't update last so we get a longer baseline
            } else {
                $diff = $amount-$lastamount;
                $timediff = $reserve->created_at->diffInSeconds($last->created_at);
                if ($timediff < 86400) {
                    $slopes[] = (int)($timediff/$diff); // seconds per tonne
                }
                // else too far apart, might be intervening states
                $last = $reserve; // continue checking
            }
        }
        if (count($slopes) < 10) {
            // insufficient data
            return null;
        }
//        $this->line("Checking: ".$commodity->name." at ".$station->name." in ".$state->name);
        $avgrate = floor($this->median($slopes));
        $regentime = $avgrate * $max;
//        $this->line(($sign>0?"Supply":"Demand")." Regen time: $regentime (".round($regentime/86400, 1).") days");
        return $regentime * $sign;
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

    private function median($arr) {
        return $this->percentile($arr, 0.5);
    }

    
    private function runEconomySizeAnalysis() {
        /* Hydrogen Fuel appears to be a non-specialised good with
         * the exception of Colony, which we only have one of
         * anyway. Empirically, the size of the economy corresponds
         * roughly to baseline (HFuel/43)^2 - which for single-station
         * systems is approximately the population, and for
         * multi-station systems gets complicated especially for
         * secondary stations. */
        
        $stations = $stations = Station::whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('economy', function($q) {
            // ignore stations with damaged economies
            // as they'll confuse the analysis
            $q->baseline();
            // hybrids are fine for this, though
        })->with('economy')->with('baselinestocks')->get();

        $hydrogen = Commodity::where('name', 'HydrogenFuel')->first();

        $commodities = Commodity::get();

        $cdata = [
            'supply'=>[],
            'demand'=>[]
        ];
        
        foreach ($stations as $station) {
            $hfuelbaseline = $station->baselinestocks->where('commodity_id', $hydrogen->id)->first();
            if (!$hfuelbaseline) {
                continue;
            }
            $hfb = $hfuelbaseline->reserves;
            if (!$hfb) {
                continue;
            }
            
            if ($station->economy->name == "Colony") {
                $esf = $this->colonysizefactor;
            } else {
                $esf = $this->genericsizefactor;
            }
            
            $economysize = ($hfb*$hfb)/($esf);
            $station->economysize = $economysize;
            $station->save();

            $ecsizefactor = sqrt($economysize);

            /* This calculates the intensity of production/consumption
             * of each good relative to the baseline set by HFuel. */
            
            foreach ($commodities as $commodity) {
                $baseline = $station->baselinestocks->where('commodity_id', $commodity->id)->first();
                if ($baseline) {
                    $intensity = $baseline->reserves / $ecsizefactor;
                    if ($intensity > 0) {
                        if (!isset($cdata['supply'][$commodity->id])) {
                            $cdata['supply'][$commodity->id] = [];
                        }
                        $cdata['supply'][$commodity->id][] = $intensity;
                    } else if ($intensity < 0) {
                        if (!isset($cdata['demand'][$commodity->id])) {
                            $cdata['demand'][$commodity->id] = [];
                        }
                        $cdata['demand'][$commodity->id][] = -$intensity;
                    }
                    $baseline->intensity = $intensity;
                    $baseline->save();
                }
            }
        }

        foreach ($commodities as $commodity) {
            $demand = isset($cdata['demand'][$commodity->id])?$cdata['demand'][$commodity->id]:[];
            $supply = isset($cdata['supply'][$commodity->id])?$cdata['supply'][$commodity->id]:[];
            $dstats = $this->stats($demand);
            $sstats = $this->stats($supply);

            $stat = Commoditystat::firstOrNew(['commodity_id' => $commodity->id]);
            
            $stat->demandmin = $dstats['min'];
            $stat->demandlowq = $dstats['lowq'];
            $stat->demandmed = $dstats['median'];
            $stat->demandhighq = $dstats['highq'];
            $stat->demandmax = $dstats['max'];
            $stat->supplymin = $sstats['min'];
            $stat->supplylowq = $sstats['lowq'];
            $stat->supplymed = $sstats['median'];
            $stat->supplyhighq = $sstats['highq'];
            $stat->supplymax = $sstats['max'];

            $stat->save();
        }
    }



    private function stats($arr) {
        if (count($arr) == 0) {
            return [
                'min' => null,
                'max' => null,
                'lowq' => null,
                'median' => null,
                'highq' => null
            ];
        }
        sort($arr);
        return [
            'min' => $arr[0],
            'max' => $arr[count($arr)-1],
            'lowq' => $this->lowq($arr),
            'median' => $this->median($arr),
            'highq' => $this->highq($arr)
        ];
    }

    private function percentile($arr, $per) {
        sort($arr);
        $mid = (count($arr)-1)*$per;
        if ($mid == floor($mid)) {
            return $arr[$mid];
        } else {
            return ($arr[floor($mid)]+$arr[ceil($mid)])/2;
        }
    }
    
    private function lowq($arr) {
        return $this->percentile($arr, 0.25);
    }
    private function highq($arr) {
        return $this->percentile($arr, 0.75);
    }

}
