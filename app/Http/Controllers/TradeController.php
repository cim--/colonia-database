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
        $stations = Station::with('system', 'economy', 'faction')->whereHas('stationclass', function($q) {
            $q->where('hasSmall', 1)
              ->orWhere('hasMedium', 1)
              ->orWhere('hasLarge', 1);
        })->orderBy('name')->get();

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
                        if (!$influence || !in_array($influence->state_id, $sparam)) {
                            continue;
                        }
                    }
                    $inf = $station->faction->currentInfluence($station->system);
                    if ($inf) {
                        $station->stateicon = $inf->state->icon;
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
                $q->where('current', true);
            })->with(['reserves' => function($q) {
                $q->where('current', true);
                }, 'reserves.station', 'reserves.station.economy', 'effects'])
               ->orderBy('name')->get();

        $cdata = [];
        $total = 0;
        $tradetotal = 0;
        $stocktotal = 0;
        $demandtotal = 0;
        $nominalstocktotal = 0;
        $nominaldemandtotal = 0;
        $stations = [];
        $oldest = Carbon::now();
        foreach ($commodities as $commodity) {
            $crow = [];
            $crow['id'] = $commodity->id;
            $crow['name'] = $commodity->displayName();
            $crow['category'] = $commodity->category;

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
            foreach ($commodity->reserves as $reserve) {
                if (!isset($stations[$reserve->station_id])) {
                    $stations[$reserve->station_id] = $reserve->station->currentStateID();
                }
                $effect = $commodity->effectForStateID($stations[$reserve->station_id]);
                $supplyfactor = ($effect&&$effect->supplysize) ? (1/$effect->supplysize) : 1;
                $demandfactor = ($effect&&$effect->demandsize) ? (1/$effect->demandsize) : 1;
                
                $total += $reserve->reserves;
                if ($reserve->reserves > 0) {
                    $stock += $reserve->reserves;
                    $nominalstock += $reserve->reserves * $supplyfactor;
                    $exported[$reserve->station->economy->id] = $reserve->station->economy;
                    if ($bestbuy === null || $bestbuy > $reserve->price) {
                        if ($reserve->price !== null) {
                            $bestbuy = $reserve->price;
                            $bestbuyplace = $reserve->station->name;
                        }
                    }
                } else {
                    $demand -= $reserve->reserves;
                    $nominaldemand -= $reserve->reserves * $demandfactor;
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
            if ($crow['supplycycle'] !== null) {
                $crow['cycstock'] = floor($stock/$crow['supplycycle']);
            } else {
                $crow['cycstock'] = null;
            }
            if ($crow['demandcycle'] !== null) {
                $crow['cycdemand'] = floor($demand/$crow['demandcycle']);
            } else {
                $crow['cycdemand'] = null;
            }
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
            $nominalstocktotal += $nominalstock;
            $nominaldemandtotal += $nominaldemand;
            
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
            'stations' => count($stations),
            'totalstations' => $totalstations,
            'oldest' => $oldest
        ]);
    }

    public function commodity(Commodity $commodity) {
        $reserves = $commodity->reserves()->where('current', true)->with('station', 'station.system', 'station.economy')->get();

        return view('trade/commodity', [
            'commodity' => $commodity,
            'reserves' => $reserves,
            'station' => null
        ]);
        
    }

    public function commodityWithReference(Commodity $commodity, Station $station) {
        $reserves = $commodity->reserves()->where('current', true)->with('station', 'station.system', 'station.economy')->get();

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
}
