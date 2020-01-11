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
        $commodities = Commodity::whereHas('reserves', function($q) {
            $q->current();
        })->with(['reserves' => function($q) {
            $q->where('current', true);
        }, 'reserves.station', 'reserves.station.economy', 'reserves.station.faction', 'effects', 'baselinestocks'])
                     ->orderBy('name')->get();

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
                }
                
                $total += $reserve->reserves;
                if ($reserve->reserves > 0) {
                    $stock += $reserve->reserves;
                    $exported[$reserve->station->economy->id] = $reserve->station->economy;
                    if ($bestbuy === null || $bestbuy > $reserve->price) {
                        if ($reserve->price !== null) {
                            $bestbuy = $reserve->price;
                            $bestbuyplace = $reserve->station->name;
                        }
                    }
                } else {
                    $demand -= $reserve->reserves;
                    $imported[$reserve->station->economy->id] = $reserve->station->economy;
                    if ($bestsell === null || $bestsell < $reserve->price) {
                        if ($reserve->price !== null) {
                            $bestsell = $reserve->price;
                            $bestsellplace = $reserve->station->name;
                        }
                    }
                }
                if ($reserve->date->lt($oldest)) {
                    $oldest = $reserve->date;
                }
            }
            $crow['stock'] = $stock;
            $crow['demand'] = $demand;
            $crow['supplycycle'] = $commodity->supplycycle ? round($commodity->supplycycle/86400,1) : null;
            $crow['demandcycle'] = $commodity->demandcycle ? round(-$commodity->demandcycle/86400,1) : null;
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
                $q->where('current', true);
        })->orderBy('name')->get();

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
        $reserves = Reserve::where('commodity_id', $commodity->id)->orderBy('created_at');

        $supply = [];
        $demand = [];
        $stations = [];
        $lastsupply = 0;
        $lastdemand = 0;
        /* Roll up commodity reserves */
        foreach ($reserves->cursor() as $reserve) {
            $station = $reserve->station_id;
            $amount = $reserve->reserves;
            $timestamp = $reserve->created_at->toIso8601String();
            if (!isset($stations[$station])) {
                $stations[$station] = $amount;
                if ($amount > 0) {
                    $supply[$timestamp] = $lastsupply + $amount;
                    $lastsupply = $supply[$timestamp];
                } else {
                    $demand[$timestamp] = $lastdemand - $amount;
                    $lastdemand = $demand[$timestamp];
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
                    $supply[$timestamp] = $lastsupply - $last + $amount;
                    $lastsupply = $supply[$timestamp];
                } else {
                    $demand[$timestamp] = $lastdemand + $last - $amount;                    
                    $lastdemand = $demand[$timestamp];
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

        $minrange = Carbon::parse($request->input('minrange', '3303-12-24'));
        $maxrange = Carbon::parse($request->input('maxrange', '3400-01-01'));

        $minrange->year -= 1286;
        $maxrange->year -= 1286;

        if ($maxrange->isFuture()) {
            $maxrange = Carbon::now();
        }
        if ($minrange->gt($maxrange)) {
            $minrange = $maxrange->copy()->subDay();
        }
        $maxrangecomp = $maxrange->copy()->addDay();

        foreach ($supply as $date => $amount) {
            $datestamp = Carbon::parse($date);
            if ($datestamp->gte($minrange) && $datestamp->lte($maxrangecomp)) {
                $datasets['supply']['data'][] = [
                    'x' => \App\Util::graphDisplayDateTime($datestamp),
                    'y' => (int)$amount
                ];
            }
        }
        foreach ($demand as $date => $amount) {
            $datestamp = Carbon::parse($date);
            if ($datestamp->gte($minrange) && $datestamp->lte($maxrangecomp)) {
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
            'maxrange' => $maxrange
        ]);
    }

    public function specialisation() {
        $commodities = Commodity::whereHas('commoditystat')->with('commoditystat')->orderBy('category')->orderBy('name')->get();
        $economies = Economy::analyse()->whereHas('stations')->orderBy('name')->get();
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
}
