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

class GoodsAnalysis2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:goodsanalysis2';

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

        $commodities = Commodity::orderBy('name')->get();

        $states = State::where('name', '!=', 'Lockdown')->orderBy('name')->get();

        foreach ($commodities as $commodity) {
            $demandregen = [];
            $supplyregen = [];
//            $this->info("Commodity: ".trim($commodity->description));
            foreach ($stations as $station) {
                foreach ($states as $state) {
                    $regen = $this->analyseRegeneration($commodity, $station, $state, $demandregen==0, $supplyregen==0);
                    if ($regen !== null) {
                        if ($regen > 0) {
                            $supplyregen[] = $regen;
                        } else if ($regen < 0) {
                            $demandregen[] = $regen;
                        }
                    }
                }
            }
            if (count($supplyregen)>0) {
                $commodity->supplycycle = $this->median($supplyregen);
            } else {
                $commodity->supplycycle = null;
            }
            if (count($demandregen)>0) {
                $commodity->demandcycle = $this->median($demandregen);
            } else {
                $commodity->demandcycle = null;
            }
            $commodity->save();
        }
        
    }

    public function analyseRegeneration(Commodity $commodity, Station $station, State $state) {
        
        $reservesquery = Reserve::where('price', '!=', null)
            ->where('station_id', $station->id)
            ->where('commodity_id', $commodity->id)
            ->where('state_id', $state->id)
            ->where('reserves', '!=', 0)
            ->where('reserves', '>', -100000) // ignore high CG demands
            ->where('date', '>', '2018-03-01') // changes in 3.0
            ->where(function ($q) {
                // ignore the tax-break week
                $q->where('date', '>=', '2018-06-01')
                  ->orWhere('date', '<', '2018-05-24');
            });
        
        $reserves = $reservesquery->get();

        if ($reserves->count() == 0) {
            return null;
        }

        $sign = ($reserves[0]->reserves > 0)?1:-1;

        $max = 0;
        $stability = 0;
        foreach ($reserves as $reserve) {
            if (abs($reserve->reserves) > $max) {
                $max = abs($reserve->reserves);
                $stability = 1;
            } else if (abs($reserve->reserves) == $max) {
                $stability++;
            }
        }
        if ($stability < 25) {
            // unlikely to have enough datapoints elsewhere for a good analysis
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
        $avgrate = (int)$this->median($slopes);
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
        sort($arr);
        $mid = (count($arr)-1)/2;
        if ($mid == floor($mid)) {
            return $arr[$mid];
        } else {
            return floor(($arr[floor($mid)]+$arr[ceil($mid)])/2);
        }
    }

    
}
