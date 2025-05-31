<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\State;
use App\Models\Economy;
use App\Models\Station;
use App\Models\Commodity;
use App\Models\Reserve;
use App\Models\Effect;
use App\Models\Tradebalance;

class TradeController extends Controller
{
    
    public function index(Request $request) {
        $stations = Station::with('system', 'economy', 'faction')->dockable()->present()->orderBy('name')->get();

        $economies = Economy::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $search = null;
        $reference = null;
        $eparam = [];
        $sparam = [];
        if ($request->input('reference')) {
            $search = [];
            $eparam = $request->input('e', []);
            $sparam = $request->input('s', []);
            $reference = $request->input('reference');
            
            foreach ($stations as $station) {
                if ($station->id != $reference) {
                    if (count($eparam)) {
                        if (!in_array($station->economy_id, $eparam)) {
                            continue;
                        }
                    }
                    if (count($sparam)) {
                        $influence = $station->faction->currentInfluence($station->system);
                        if (!$influence) {
                            continue;
                        }
                        $found = false;
                        foreach ($influence->states as $state) {
                            if (in_array($state->id, $sparam)) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            continue;
                        }
                    }
                    $inf = $station->faction->currentInfluence($station->system);
                    if ($inf) {
                        $station->stateicon = $inf->states;
                    }
                    
                    $search[] = $station;
                }
            }
            $reference = Station::with('system')->find($reference);
        }

        return view('trade/index', [
            'stations' => $stations,
            'economies' => $economies,
            'states' => $states,
            'search' => $search,
            'reference' => $reference,
            'eparam' => $eparam,
            'sparam' => $sparam,
        ]);
    }

    public function reserves() {
        ini_set('memory_limit',512*1024*1024).
        
        $commodities = Commodity::whereHas('reserves', function($q) {
            $q->normalStation();
        })->with(['reserves' => function($q) {
            $q->normalStation();
        }, 'reserves.station', 'reserves.station.economy', 'reserves.station.faction', 'effects', 'baselinestocks'])
                     ->orderBy('description')->get();

        $cdata = [];
        $total = 0;
        $tradetotal = 0;
        $stocktotal = 0;
        $demandtotal = 0;
        $nominalstocktotal = 0;
        $nominaldemandtotal = 0;
        $nominaldailystocktotal = 0;
        $nominaldailydemandtotal = 0;
        $stations = [];
        $oldest = Carbon::now();
        foreach ($commodities as $commodity) {
            $crow = [];
            $crow['id'] = $commodity->id;
            $crow['name'] = $commodity->displayName();
            $crow['category'] = $commodity->category;
            $crow['average'] = $commodity->averageprice;

            $stock = 0;
            $demand = 0;
            $nominalstock = 0;
            $nominaldemand = 0;
            $bestbuy = null;
            $bestsell = null;
            $bestbuyplace = null;
            $bestsellplace = null;
            $imported = [];
            $exported = [];
            $crow['baselinedemand'] = $commodity->baselinestocks->where('reserves', '<', 0)->sum('reserves');
            $crow['baselinestock'] = $commodity->baselinestocks->where('reserves', '>', 0)->sum('reserves');
            $nominalstocktotal += $crow['baselinestock'];
            $nominaldemandtotal += $crow['baselinedemand'];
            
            foreach ($commodity->reserves as $reserve) {
                if (!isset($stations[$reserve->station_id])) {
                    $stations[$reserve->station_id] = 1;
                    //                    if ($reserve->station->marketStateChange()) {
                    //                        $stations[$reserve->station_id] = 2;
                    //                    }
                }
                
                $total += $reserve->reserves;
                if ($reserve->reserves > 0) {
                    $stock += $reserve->reserves;
                    $exported[$reserve->station->economy->id] = $reserve->station->economy;
                    if ($stations[$reserve->station_id] == 1) {
                    if ($bestbuy === null || $bestbuy > $reserve->price) {
                        if ($reserve->price !== null) {
                            $bestbuy = $reserve->price;
                            $bestbuyplace = $reserve->station->name;
                        }
                    }
                    }
                } else {
                    $demand -= $reserve->reserves;
                    $imported[$reserve->station->economy->id] = $reserve->station->economy;
                    if ($stations[$reserve->station_id] == 1) {
                    if ($bestsell === null || $bestsell < $reserve->price) {
                        if ($reserve->price !== null) {
                            $bestsell = $reserve->price;
                            $bestsellplace = $reserve->station->name;
                        }
                    }
                    }
                }
                if ($reserve->date->lt($oldest)) {
                    $oldest = $reserve->date;
                }
            }
            $crow['stock'] = $stock;
            $crow['demand'] = $demand;
            $crow['supplycycle'] = $commodity->supplycycle ? max(round($commodity->supplycycle/86400,3),0.001) : null;
            $crow['demandcycle'] = $commodity->demandcycle ? max(round(-$commodity->demandcycle/86400,3),0.001) : null;
            if ($crow['supplycycle'] !== null && count($exported) > 0) {
                $crow['cycstock'] = floor($crow['baselinestock']/$crow['supplycycle']);
                $nominaldailystocktotal += $crow['cycstock'];
            } else {
                $crow['cycstock'] = null;
            }
            if ($crow['demandcycle'] !== null) {
                $crow['cycdemand'] = -floor($crow['baselinedemand']/$crow['demandcycle']);
                $nominaldailydemandtotal += $crow['cycdemand'];

            } else {
                $crow['cycdemand'] = null;
            }
            $crow['cycestimate'] = $commodity->cycleestimate;
            $crow['exported'] = $exported;
            $crow['imported'] = $imported;
            $crow['buy'] = $bestbuy;
            $crow['sell'] = $bestsell;
            $crow['buyplace'] = $bestbuyplace;
            $crow['sellplace'] = $bestsellplace;
            if ($bestbuyplace != null) {
                $tradetotal += $stock - $demand;
            }
            $stocktotal += $stock;
            $demandtotal += $demand;
            
            $cdata[] = $crow;
        }

        $totalstations = Station::whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('facilities', function($q) {
            $q->where('name', 'Commodities');
        })->count();
        
        return view('trade/reserves', [
            'commodities' => $cdata,
            'total' => $total,
            'tradetotal' => $tradetotal,
            'stocktotal' => $stocktotal,
            'demandtotal' => $demandtotal,
            'nominalstocktotal' => $nominalstocktotal,
            'nominaldemandtotal' => $nominaldemandtotal,
            'cyclicstocktotal' => $nominaldailystocktotal,
            'cyclicdemandtotal' => $nominaldailydemandtotal,
            'cyclictotal' => $nominaldailystocktotal-$nominaldailydemandtotal,
            'stations' => count($stations),
            'totalstations' => $totalstations,
            'oldest' => $oldest
        ]);
    }

    public function commodity(Commodity $commodity) {
        $reserves = $commodity->reserves()->current()->with('station', 'station.system', 'station.economy')->get();
        $commodity->load('baselinestocks');
        return view('trade/commodity', [
            'commodity' => $commodity,
            'reserves' => $reserves,
            'station' => null
        ]);
        
    }

    public function commodityWithReference(Commodity $commodity, Station $station) {
        $reserves = $commodity->reserves()->current()->with('station', 'station.system', 'station.economy')->get();

        return view('trade/commodity', [
            'commodity' => $commodity,
            'reserves' => $reserves,
            'station' => $station
        ]);
        
    }

    public function effects() {
        $states = State::orderBy('name')->get();
        
        $commodities = Commodity::whereHas('reserves', function($q) {
                $q->where('current', true)
                    ->where('reserves', '!=', 0);
        })->orderBy('description')->get();

        $economies = Economy::where('analyse', true)->orderBy('name')->get();

        $balances = Tradebalance::get();
        $bdata = [];
        foreach ($economies as $economy) {
            $bdata[$economy->id] = [];
        }
        foreach ($balances as $balance) {
            $bdata[$balance->economy_id][$balance->state_id] = $balance;
        }
        
        return view('trade/effects', [
            'commodities' => $commodities,
            'states' => $states,
            'economies' => $economies,
            'balances' => $bdata
        ]);
    }

    public function effectsCommodity(Commodity $commodity) {
        $states = State::orderBy('name')->get();
        
        $effects = Effect::where('commodity_id', $commodity->id)->get();

        return view('trade/effectscommodity', [
            'commodity' => $commodity,
            'states' => $states,
            'effects' => $effects
        ]);
    }

    public function effectsState(State $state) {
        $commodities = Commodity::whereHas('reserves', function($q) {
                $q->where('current', true);
        })->orderBy('name')->get();
        
        $effects = Effect::where('state_id', $state->id)->get();

        return view('trade/effectsstate', [
            'state' => $state,
            'commodities' => $commodities,
            'effects' => $effects
        ]);
    }

    public function commodityHistory(Request $request, Commodity $commodity) {
        list ($minrange, $maxrange, $maxrangecomp) = \App\Util::graphRanges();

        // don't need to start more than 2 weeks back
        $mincheck = $minrange->copy()->subWeeks(2);

        $reserves = Reserve::where('commodity_id', $commodity->id)
                  ->whereDate('created_at', '>=', $mincheck)
                  ->orderBy('created_at');
        
        $supply = [];
        $demand = [];
        $stations = [];
        $laststations = [];
        $lastsupply = 0;
        $lastdemand = 0;
        $lastepoch = 0;
        $epochs = Reserve::epochs();
        $epochs2 = Reserve::epochs2();
        /* Roll up commodity reserves */
        $cidx = 0;
        foreach ($reserves->cursor() as $reserve) {
            $cidx++;
            $station = $reserve->station_id;
            $amount = $reserve->reserves;
            $timestamp = $reserve->created_at->toIso8601String();
            $date = $reserve->created_at->format('Y-m-d');
            $laststations[$station] = $reserve->created_at;
            $epochindex = array_search($date, $epochs2);
            if ($epochindex !== false) {
                if ($date != $lastepoch) {
                    // reset tracking
                    $lastepoch = $date;
                    $epochstart = Carbon::parse($epochs[$epochindex]);
                    $unsets = [];
                    foreach ($stations as $idx => $discard) {
                        if ($laststations[$idx]->lt($epochstart)) {
                            if ($stations[$idx] > 0) {
                                $lastsupply -= $stations[$idx];
                            } else {
                                $lastdemand += $stations[$idx];
                            }
                            $unsets[] = $idx;
                        }
                    }
                    foreach ($unsets as $idx) {
                        unset($stations[$idx]);
                        unset($laststations[$idx]);
                    }
                }
            } else if ($cidx % 1000 == 0) {
                // throw out old data every so often
                $epochstart = $reserve->created_at->copy()->subDays(14);
                $unsets = [];
                foreach ($stations as $idx => $discard) {
                    if ($laststations[$idx]->lt($epochstart)) {
                        if ($stations[$idx] > 0) {
                            $lastsupply -= $stations[$idx];
                        } else {
                            $lastdemand += $stations[$idx];
                        }
                        $unsets[] = $idx;
                    }
                }
                foreach ($unsets as $idx) {
                    unset($stations[$idx]);
                    unset($laststations[$idx]);
                }
                
            }
            if (!isset($stations[$station])) {
                $stations[$station] = $amount;
                if ($amount > 0) {
                    $supply[$date] = $lastsupply + $amount;
                    $lastsupply = $supply[$date];
                } else if ($amount < 0) {
                    $demand[$date] = $lastdemand - $amount;
                    $lastdemand = $demand[$date];
                }
            } else {
                $last = $stations[$station];
                if ($last*$amount < 0) {
                    continue;
                } else if ($last == $amount) {
                    continue; // ignore duplicates
                }
                if ($amount == 0) {
                    if ($last > 0) {
                        $amount = 0.000001;
                    } else {
                        $amount = -0.000001;
                    }
                }
                if ($amount > 0) {
                    $supply[$date] = $lastsupply - $last + $amount;
                    $lastsupply = $supply[$date];
                } else {
                    $demand[$date] = $lastdemand + $last - $amount;
                    $lastdemand = $demand[$date];
                }
                $stations[$station] = $amount;
            }
        }

        $datasets = [
            'supply' => [
                'label' => "Supply",
                'backgroundColor' => 'transparent',
                'borderColor' => '#009000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'supply',
            ],
            'demand' => [
                'label' => "Demand",
                'backgroundColor' => 'transparent',
                'borderColor' => '#900000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'demand',
            ]
        ];

        

        foreach ($supply as $date => $amount) {
            $datestamp = Carbon::parse($date);
            if ($datestamp->gte($minrange) && $datestamp->lt($maxrangecomp)) {
                $datasets['supply']['data'][] = [
                    'x' => \App\Util::graphDisplayDateTime($datestamp),
                    'y' => (int)$amount
                ];
            }
        }
        foreach ($demand as $date => $amount) {
            $datestamp = Carbon::parse($date);
            if ($datestamp->gte($minrange) && $datestamp->lt($maxrangecomp)) {
                $datasets['demand']['data'][] = [
                    'x' => \App\Util::graphDisplayDateTime($datestamp),
                    'y' => (int)$amount
                ];
            }
        }
        sort($datasets); // compact

        $chart = app()->chartjs
                ->name("reservehistory")
                ->type("line")
                ->size(["height" => 400, "width"=>1000])
                ->datasets($datasets)
                ->options(['scales' => [
                    'xAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'bottom',
                            'ticks' => [
                                'callback' => "@@chart_xaxis_callback_datetime@@"
                            ]
                        ]
                    ],
                    'yAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'left',
                            'scaleLabel' => [
                                'labelString' => "Supply",
                                'display' => true
                            ],
                            'ticks' => [
                                'min' => 0
                            ],
                            'id' => 'supply'
                        ],
                        [
                            'type' => 'linear',
                            'position' => 'right',
                            'scaleLabel' => [
                                'labelString' => "Demand",
                                'display' => true
                            ],
                            'ticks' => [
                                'min' => 0
                            ],
                            'id' => 'demand'
                        ]
                    ]
                ],
                'tooltips' => [
                    'callbacks' => [
                        'title' => "@@tooltip_label_datetime_title@@",
                        'label' => "@@tooltip_label_datetime@@"
                    ]
                ] 
                ]
                ); 
        
        return view('trade/reservehistory', [
            'commodity' => $commodity,
            'chart' => $chart,
            'minrange' => $minrange,
            'maxrange' => $maxrange,
            'mode' => 'volume'
        ]);
    }

    private function priceBands($imports, $exports, $lasttime) {
        $threshold = Carbon::parse($lasttime)->subDays(14);

        if (count($exports) == 0) {
            return [
                'el' => null,
                'eh' => null,
                'il' => $this->minPrice($imports, $threshold),
                'ih' => $this->maxPrice($imports, $threshold)
            ];
        } else if (count($imports) == 0) {
            return [
                'el' => $this->minPrice($exports, $threshold),
                'eh' => $this->maxPrice($exports, $threshold),
                'il' => null,
                'ih' => null
            ];
        } else {
            return [
                'el' => $this->minPrice($exports, $threshold),
                'eh' => $this->maxPrice($exports, $threshold),
                'il' => $this->minPrice($imports, $threshold),
                'ih' => $this->maxPrice($imports, $threshold)
            ];
        }
    }

    public function minPrice($list, $threshold)
    {
        //        dd($list);
        return collect($list)->filter(function($v) use ($threshold) {
            return $threshold->lt($v->created_at);
        })->min('price');
    }

    public function maxPrice($list, $threshold)
    {
        return collect($list)->filter(function($v) use ($threshold) {
            return $threshold->lt($v->created_at);
        })->max('price');
    }

    
    public function commodityPriceHistory(Request $request, Commodity $commodity) {
        list ($minrange, $maxrange, $maxrangecomp) = \App\Util::graphRanges();

        // don't need to start more than 2 weeks back
        $mincheck = $minrange->copy()->subWeeks(2);
        
        $reserves = Reserve::where('commodity_id', $commodity->id)
                  ->whereDate('created_at', '>=', $mincheck)
                  ->orderBy('created_at');

        $dates = [];
        $exports = [];
        $imports = [];
        $lasttime = 0;
        $epochs = Reserve::epochs2();
        /* Roll up commodity reserves */
        foreach ($reserves->cursor() as $reserve) {
            if ($reserve->price == 0) {
                continue;
                // can't really tell if this is actually a zero or
                // just an untraded good
            } else if ($reserve->reserves == -1 || $reserve->reserves == 0) {
                // may be an untraded good
                continue;
            }
            $station = $reserve->station_id;
            $price = $reserve->price;
            $supply = ($reserve->reserves > 0);
            $timestamp = $reserve->created_at->format("Y-m-d");

            if ($lasttime != $timestamp) {
                if ($lasttime != 0) {
                    $dates[$lasttime] = $this->priceBands($imports, $exports, $lasttime);
                    if (in_array($timestamp, $epochs)) {
                        // major change in behaviour, reset old data
                        $imports = [];
                        $exports = [];
                    }
                }
                $lasttime = $timestamp;
            }

            
            if ($supply) {
                $list = 'exports';
                $other = 'imports';
            } else {
                $list = 'imports';
                $other = 'exports';
            }
            if (!isset($$list[$station])) {
                $$list[$station] = $reserve;
                unset($$other[$station]);
            } else {
                $$list[$station] = $reserve;
            }
        }
        // and finally
        if ($lasttime != 0) {
            $dates[$lasttime] = $this->priceBands($imports, $exports, $lasttime);
        }

        
        $datasets = [
            'il' => [
                'label' => "Minimum Import Price",
                'backgroundColor' => 'transparent',
                'borderColor' => '#009000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'price',
            ],
            'ih' => [
                'label' => "Maximum Import Price",
                'backgroundColor' => 'transparent',
                'borderColor' => '#006000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'price',
            ],
            'el' => [
                'label' => "Minimum Export Price",
                'backgroundColor' => 'transparent',
                'borderColor' => '#600000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'price',
            ],
            'eh' => [
                'label' => "Maximum Export Price",
                'backgroundColor' => 'transparent',
                'borderColor' => '#900000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'price',
            ],
        ];



        foreach ($dates as $date => $info) {
            $datestamp = Carbon::parse($date);
            if ($datestamp->gte($minrange) && $datestamp->lt($maxrangecomp)) {
                foreach (['il','ih','el','eh'] as $series) {
                    if ($info[$series] != null) {
                        $datasets[$series]['data'][] = [
                            'x' => \App\Util::graphDisplayDateTime($datestamp),
                            'y' => (int)$info[$series]
                        ];
                    }
                }
            }
        }
        
        sort($datasets); // compact

        $chart = app()->chartjs
                ->name("reservehistory")
                ->type("line")
                ->size(["height" => 400, "width"=>1000])
                ->datasets($datasets)
                ->options(['scales' => [
                    'xAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'bottom',
                            'ticks' => [
                                'callback' => "@@chart_xaxis_callback_datetime@@"
                            ]
                        ]
                    ],
                    'yAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'left',
                            'scaleLabel' => [
                                'labelString' => "Price",
                                'display' => true
                            ],
                            'ticks' => [
                                'min' => 0
                            ],
                            'id' => 'price'
                        ]
                    ]
                ],
                'tooltips' => [
                    'callbacks' => [
                        'title' => "@@tooltip_label_datetime_title@@",
                        'label' => "@@tooltip_label_datetime@@"
                    ]
                ] 
                ]
                ); 
        
        return view('trade/reservehistory', [
            'commodity' => $commodity,
            'chart' => $chart,
            'minrange' => $minrange,
            'maxrange' => $maxrange,
            'mode' => 'price'
        ]);
    }

    public function specialisation() {
        $gcommodities = Commodity::whereHas('commoditystat')->with('commoditystat')->orderBy('category')->orderBy('name')->get();
        $economies = Economy::analyse()->whereHas('stations')->with('stations', function($q) {
            $q->notFactory();
        })->orderBy('name')->get();

        $commodities = [];
        foreach ($gcommodities as $commodity) {
            $commodity->exports = [];
            $commodity->imports = [];
            $commodities[$commodity->id] = $commodity;
        }

        foreach ($economies as $economy) {
            foreach ($economy->stations as $station) {
                $reserves = $station->reserves()->where('current', 1)->get();
                foreach ($reserves as $reserve) {
                    if ($reserve->reserves > 0) {
                        $exports = $commodities[$reserve->commodity_id]->exports;
                        $exports[$economy->id] = $economy;
                        $commodities[$reserve->commodity_id]->exports = $exports;
                    } else if ($reserve->reserves < 0) {
                        $imports = $commodities[$reserve->commodity_id]->imports;
                        $imports[$economy->id] = $economy;
                        $commodities[$reserve->commodity_id]->imports = $imports;
                    }
                }
            }
        }
        
        
        return view('trade/specialisation', [
            'commodities' => $commodities,
            'economies' => $economies
        ]);
    }

    public function specialisationEconomy(Economy $economy) {
        $stations = Station::present()->where('economy_id', $economy->id)->whereHas('baselinestocks')->with('baselinestocks', 'baselinestocks.commodity', 'baselinestocks.commodity.commoditystat')->orderBy('name', 'desc')->get();

        $sys = [];
        foreach ($stations as $idx => $station) {
//            $sys[$station->id] = 5+(10*$idx);
            $sys[$station->id] = $idx;
            $slabels[$idx] = $station->displayName();
        }

        $commodities = Commodity::whereHas('baselinestocks', function($q) use ($economy) {
            $q->whereHas('station', function ($q2) use ($economy) {
                $q2->where('economy_id', $economy->id);
            });
        })->where('category', '!=', 'Salvage')->orderBy('category')->orderBy('description')->get();
        $cxs = [];
        foreach ($commodities as $idx => $commodity) {
            $cxs[$commodity->id] = $idx;
            $clabels[$idx] = trim($commodity->description);
        }
        
        $imports = [];
        $exports = [];
        $idescs = ['', 'Very Low', 'Low', 'Average', 'High', 'Very High'];
        foreach ($stations as $station) {
            foreach ($station->baselinestocks as $baseline) {
                if ($baseline->commodity->category == "Salvage") {
                    continue;
                }
                $x = $baseline->commodity_id;
                $y = $station->id;
                $r = $baseline->commodity->commoditystat->getLevel($baseline->intensity);
                if (!isset($cxs[$x])) {
                    // can happen with some rares
                    continue;
                }
                $point = [
                    'x' => $cxs[$x],
                    'y' => $sys[$y],
                    'r' => $r*2,
                    'desc' => $station->displayName()." -- ".trim($baseline->commodity->description),
                    'intensity' => $idescs[$r]
                ];
                if ($baseline->intensity > 0) {
                    $exports[] = $point;
                } else {
                    $imports[] = $point;
                }
            }
        }
        
        $datasets = [
            [
                'backgroundColor' => 'transparent',
                'borderColor' => '#009000',
                'fill' => false,
                'data' => $imports,
                'label' => "Imports"
            ],
            [
                'backgroundColor' => 'transparent',
                'borderColor' => '#900000',
                'fill' => false,
                'data' => $exports,
                'label' => "Exports"
            ]
        ];
        
        
        $options = [
            'scales' => [
                'xAxes' => [
                    [
                        'type' => 'linear',
                        'position' => 'bottom',
                        'ticks' => [
                            'min' => -1,
                            'stepSize' => 1,
                            'maxTicksLimit' => 1000,
                            'autoSkip' => false,
                            'callback' => "@@chart_commodity_callback@@"
                        ],
                        'gridLines' => [
                            'display' => false,
                            'drawBorder' => false
                        ],
                    ]
                ],
                'yAxes' => [
                    [
                        'type' => 'linear',
                        'position' => 'left',
                        'ticks' => [
                            'min' => -1,
                            'stepSize' => 1,
                            'autoSkip' => false,
                            'maxTicksLimit' => 1000,
                            'callback' => "@@chart_station_callback@@"
                        ],
                        'gridLines' => [
                            'display' => false,
                            'drawBorder' => false
                        ],
                    ],
                ] 
            ],
            'tooltips' => [
                'callbacks' => [
                    'title' => "@@tooltip_label_desc@@",
                    'label' => "@@tooltip_label_intensity@@"
                ]
            ] 
        ];

        $chart = app()->chartjs
                ->name("specialisationeconomy")
                ->type("bubble")
                ->size(["height" => 100+(25*$stations->count()), "width"=>1000])
                ->datasets($datasets)
                ->options($options);
        
        return view('trade/specialisationeconomy', [
            'economy' => $economy,
            'chart' => $chart,
            'slabels' => $slabels,
            'clabels' => $clabels,
        ]);
    }

    public function specialisationHybrid(Request $request) {
        $gcommodities = Commodity::whereHas('commoditystat')->with('commoditystat')->orderBy('category')->orderBy('name')->get();
        $economies = Economy::analyse()->whereHas('stations')->with('stations', function($q) {
            $q->notFactory()->dockable()->tradable();
        })->orderBy('name')->get();

        // insert data
        $terraforming = Economy::where('name', 'Terraforming')->first();
        $contraband = Economy::where('name', 'Contraband')->first(); 
        
        // set weights
        $weights = [];
        foreach ($economies as $idx => $economy) {
            $weights[$economy->id] = $request->input('eco'.$economy->id, $idx ? 0 : 1);
        }
        $weights[$terraforming->id] = $request->input('eco'.$terraforming->id, $idx ? 0 : 1);
        $weights[$contraband->id] = $request->input('eco'.$contraband->id, $idx ? 0 : 1);

        // get commodities as hash
        $commodities = [];
        foreach ($gcommodities as $commodity) {
            $commodity->exports = [];
            $commodity->imports = [];
            if (in_array(trim($commodity->description), ["Semiconductors", "Superconductors", "Polymers", "Atmospheric Processors", "Aquaponic Systems", "Land Enrichment Systems", "Synthetic Fabrics"])) {
                // imports not shared with Colony
                $imports = $commodity->imports;
                $imports[$terraforming->id] = $terraforming;
                $commodity->imports = $imports;
            }
            if (in_array(trim($commodity->description), ["Nerve Agents","Consumer Technology","Beer","Liquor","Narcotics","Onionhead Gamma Strain","Tobacco","Wine","Slaves","Battle Weapons","Landmines","Personal Weapons"])) {
                $exports = $commodity->exports;
                $exports[$contraband->id] = $contraband;
                $commodity->exports = $exports;
            }
            $commodities[$commodity->id] = $commodity;
        }
        
        // search for import and export types
        foreach ($economies as $economy) {

            foreach ($economy->stations as $station) {
                $reserves = $station->reserves()->where('current', 1)->get();
                foreach ($reserves as $reserve) {
                    if ($reserve->reserves > 0) {
                        $exports = $commodities[$reserve->commodity_id]->exports;
                        $exports[$economy->id] = $economy;
                        if ($economy->name == "Colony") {
                            // Terraforming has same exports as Colony
                            $exports[$terraforming->id] = $terraforming;
                        }
                        $commodities[$reserve->commodity_id]->exports = $exports;
                    } else if ($reserve->reserves < -1) {
                        // demand of 1 can be caused by data oddities
                        $imports = $commodities[$reserve->commodity_id]->imports;
                        $imports[$economy->id] = $economy;
                        if ($economy->name == "Colony") {
                            // Terraforming has mostly same exports as Colony
                            if (!in_array(trim($commodities[$reserve->commodity_id]->description), ["Progenitor Cells", "Performance Enhancers"])) {
                                $imports[$terraforming->id] = $terraforming;
                            }
                        }
                        $commodities[$reserve->commodity_id]->imports = $imports;
                    }
                }
            }
        }

        // calculate likely supply/demand levels for each commodity
        foreach ($commodities as $commodity) {
            $exportweight = 0;
            $importweight = 0;
            foreach ($commodity->exports as $eid => $econ) {
                $exportweight += $weights[$eid];
            }
            foreach ($commodity->imports as $eid => $econ) {
                $importweight += $weights[$eid];
            }

            if ($importweight > 0 || $exportweight > 0) {
                $positions = [
                    $exportweight * $commodity->commoditystat->supplymin - $importweight * $commodity->commoditystat->demandmax,
                    $exportweight * $commodity->commoditystat->supplylowq - $importweight * $commodity->commoditystat->demandhighq,
                    $exportweight * $commodity->commoditystat->supplymed - $importweight * $commodity->commoditystat->demandmed,
                    $exportweight * $commodity->commoditystat->supplyhighq - $importweight * $commodity->commoditystat->demandlowq,
                    $exportweight * $commodity->commoditystat->supplymax - $importweight * $commodity->commoditystat->demandmin
                ];
                $commodity->supplystats = $positions;
            }
            if ($commodity->name == "Beer") {
                //dd($commodity->name, $importweight, $exportweight, $commodity->supplystats, $commodity->imports, $weights);
            }
        }
        $economies[] = $terraforming;
        $economies[] = $contraband;
        
        return view('trade/specialisationhybrid', [
            'commodities' => $commodities,
            'economies' => $economies,
            'weights' => $weights
        ]);
    }
}
