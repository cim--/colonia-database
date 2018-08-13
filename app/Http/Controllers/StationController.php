<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Stationclass;
use App\Models\Faction;
use App\Models\Economy;
use App\Models\System;
use App\Models\Facility;
use App\Models\History;
use App\Models\Commodity;
use App\Models\Moduletype;
use App\Models\Module;
use App\Models\Reserve;
use App\Models\Baselinestock;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stations = Station::with('system', 'economy', 'stationclass', 'faction', 'faction.government', 'facilities')->get();
        //
        return view('stations/index', [
            'stations' => $stations
        ]);
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $classes = Stationclass::orderBy('name')->get();
        $factions = Faction::orderBy('name')->get();
        $economies = Economy::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();
        
        return view('stations/create', [
            'stationFacilities' => Facility::stationFacilities(),
            'classes' => \App\Util::selectMap($classes),
            'factions' => \App\Util::selectMap($factions),
            'economies' => \App\Util::selectMap($economies),
            'systems' => \App\Util::selectMap($systems, false, 'displayName')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $this->validate($request, [
            'name' => 'required',
            'planet' => 'required',
            'distance' => 'required|numeric|min:1'
        ]);

        $station = new Station();
        return $this->updateModel($request, $station);
    }

    private function updateModel(Request $request, Station $station)
    {
        $oldfaction = $station->faction_id;
        
        $station->name = $request->input('name');
        $station->system_id = $request->input('system_id');
        $station->planet = $request->input('planet');
        $station->distance = $request->input('distance');
        $station->stationclass_id = $request->input('stationclass_id');
        $station->economy_id = $request->input('economy_id');
        $station->faction_id = $request->input('faction_id');
        $station->primary = $request->input('primary', 0);
        $station->strategic = $request->input('strategic', 0);
        $station->eddb = $request->input('eddb');
        if ($request->input('gravity') !== "") {
            $station->gravity = $request->input('gravity');
        } else {
            $station->gravity = null;
        }
        $station->save();

        $states = $request->input('state');
        $facs = $request->input('facility',[]);
        $facsettings = [];
        foreach ($facs as $fac) {
            if ($states[$fac] == 1) {
                $facsettings[$fac] = ['enabled' => 1];
            } else {
                $facsettings[$fac] = ['enabled' => 0];
            }
        }
        
        $station->facilities()->sync($facsettings);

        if ($oldfaction && $oldfaction != $station->faction_id) {
            $tick = \App\Util::tick();
            // station has changed ownership
            $loss = new History;
            $loss->location_id = $station->id;
            $loss->location_type = 'App\Models\Station';
            $loss->faction_id = $oldfaction;
            $loss->date = $tick;
            $loss->expansion = false;
            $loss->description = 'lost control of';
            $loss->save();

            $gain = new History;
            $gain->location_id = $station->id;
            $gain->location_type = 'App\Models\Station';
            $gain->faction_id = $station->faction_id;
            $gain->date = $tick;
            $gain->expansion = true;
            $gain->description = 'took control of';
            $gain->save();

            /* Governance change will affect outfitting - reset */
            $station->modules()->detach();
            $station->ships()->detach();
        }
        
        return redirect()->route('stations.show', $station->id);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function show(Station $station)
    {
        return view('stations/show', [
            'station' => $station
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function edit(Station $station)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $classes = Stationclass::orderBy('name')->get();
        $factions = Faction::orderBy('name')->get();
        $economies = Economy::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();
        
        return view('stations/edit', [
            'station' => $station,
            'stationFacilities' => Facility::stationFacilities(),
            'classes' => \App\Util::selectMap($classes),
            'factions' => \App\Util::selectMap($factions),
            'economies' => \App\Util::selectMap($economies),
            'systems' => \App\Util::selectMap($systems, false, 'displayName')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Station $station)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $this->validate($request, [
            'name' => 'required',
            'planet' => 'required',
            'distance' => 'required|numeric|min:1'
        ]);

        return $this->updateModel($request, $station);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function destroy(Station $station)
    {
        //
    }


    public function trade(Station $station)
    {
        $reserves = Commodity::whereHas('reserves', function($q) use ($station) {
            $q->where('station_id', $station->id)->where('current', true);
        })->with(['reserves' => function($q) use ($station) {
            $q->where('station_id', $station->id)
              ->where('current', true);
            }])->with('commoditystat')->orderBy('name')->get();

        $supply = 0;
        $demand = 0;
        foreach ($reserves as $reserve) {
            $stock = $reserve->reserves->first()->reserves;
            if ($stock > 0) {
                $supply += $stock;
            } else {
                $demand -= $stock;
            }
        }
        
        return view('stations/trade', [
            'station' => $station,
            'reserves' => $reserves,
            'supply' => $supply,
            'demand' => $demand
        ]);
    }

    public function outfitting(Station $station)
    {
        return $this->outfittingPage($station, false);
    }
    public function outfittingCurrent(Station $station)
    {
        return $this->outfittingPage($station, true);
    }
    private function outfittingPage(Station $station, $reqcurrent) {
        $coremodules = Moduletype::where('type', 'core')->whereHas('modules.stations')->with(['modules' => function($q) use ($station, $reqcurrent) {
                $q->isAvailableAtStation($station, $reqcurrent);
        }])->orderBy('description')->get();

        $optionalmodules = Moduletype::where('type', 'optional')->whereHas('modules.stations')->with(['modules' => function($q) use ($station, $reqcurrent) { 
                $q->isAvailableAtStation($station, $reqcurrent);
        }])->orderBy('description')->get();

        $optionalnsmodules = Moduletype::where('type', 'optionalns')->with(['modules' => function($q) use ($station, $reqcurrent) {
            $q->withCount('stations');
            $q->isAvailableAtStation($station, $reqcurrent);
        }])->orderBy('description')->get();
        
        $armours = Moduletype::where('type', 'armour')->with(['modules' => function($q) use ($station, $reqcurrent) {
                $q->isAvailableAtStation($station, $reqcurrent);
        }])->orderBy('description')->get();
        $ships = Module::whereHas('moduletype', function($q) {
            $q->where('type', 'armour');
        })->get();
        $shiptypes = [];
        foreach ($ships as $ship) {
            $shiptypes[$ship->type] = $ship->type;
        }
        ksort($shiptypes);

        $weapons = Moduletype::where('type', 'hardpoint')->with(['modules' => function($q) use ($station, $reqcurrent) {
                $q->isAvailableAtStation($station, $reqcurrent);
                $q->withCount('stations');
            }])->orderBy('description')->get();

        $utilities = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($station, $reqcurrent) {
                $q->withCount('stations');
                $q->isAvailableAtStation($station, $reqcurrent);
            }])->orderBy('description')->get();
        
        $utilitiesns = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereNotIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($station, $reqcurrent) {
                $q->withCount('stations');
                $q->isAvailableAtStation($station, $reqcurrent);
            }])->orderBy('description')->get();

        
        return view('stations/outfitting', [
            'station' => $station,
            'coremodules' => $coremodules,
            'optmodules' => $optionalmodules,
            'optnsmodules' => $optionalnsmodules,
            'armours' => $armours,
            'shiptypes' => $shiptypes,
            'weapons' => $weapons,
            'utilities' => $utilities,
            'utilitiesns' => $utilitiesns,
            'reqcurrent' => $reqcurrent
        ]);
    }

    public function shipyard(Station $station)
    {
        $ships = $station->ships()->orderBy('name')->get();

        return view('stations/shipyard', [
            'station' => $station,
            'ships' => $ships
        ]);
    }

    
    public function eddb($eddb) {
        $station = Station::where('eddb', $eddb)->first();
        if (!$station) {
            \App::abort(404);
        } else {
            return redirect()->route('stations.show', $station->id);
        }
    }

    public function tradeHistory(Request $request, Station $station, Commodity $commodity) {
        $datasets = [
            'price' => [
                'label' => "Price",
                'backgroundColor' => 'transparent',
                'borderColor' => '#000090',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'price',
            ],
            'reserves' => [
                'backgroundColor' => 'transparent',
                'borderColor' => '#900000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'reserves',
            ]
        ];
        $properties = ['price', 'reserves'];

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
        
        $entries = Reserve::where('station_id', $station->id)
            ->where('commodity_id', $commodity->id)
            ->whereDate('date', '>=', $minrange)
            ->whereDate('date', '<=', $maxrange->copy()->addDay())
            ->where('price', '!=', 0)
            ->with('state')
            ->orderBy('created_at')
            ->get();
        if ($entries->count() == 0) {
            $chart = null; 
        } else {
            foreach ($entries as $idx => $entry) {
                if ($idx == 0) {
                    if ($entry->reserves > 0) {
                        $sign = 1;
                        $reservelabel = $datasets['reserves']['label'] = "Supply";
                    } else {
                        $sign = -1;
                        $reservelabel = $datasets['reserves']['label'] = "Demand";
                    }
                } else {
                    if ($entry->reserves * $sign < 0) {
                        continue;
                    }
                }
                foreach ($properties as $prop) {
                    $datasets[$prop]['data'][] = [
                        'x' => \App\Util::graphDisplayDateTime($entry->created_at),
                        'y' => abs($entry->$prop),
                        'state' => $entry->state->name
                    ];
                }
            }
            sort($datasets); // compact

            $chart = app()->chartjs
                ->name("tradehistory")
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
                            'id' => 'price',
                            'gridLines' => [
                                'display' => false
                            ],
                            'scaleLabel' => [
                                'labelString' => "Price",
                                'display' => true
                            ],
                        ],
                        [
                            'id' => 'reserves',
                            'gridLines' => [
                                'display' => false
                            ],
                            'scaleLabel' => [
                                'labelString' => $reservelabel,
                                'display' => true
                            ],
                            'position' => 'right',
                        ]
                    ]
                ],
                'tooltips' => [
                    'callbacks' => [
                        'title' => "@@tooltip_label_datetime_title@@",
                        'label' => "@@tooltip_label_datetime@@"
                    ]
                ] 
                ]); 
        }
        
        return view('stations/tradehistory', [
            'station' => $station,
            'commodity' => $commodity,
            'chart' => $chart,
            'minrange' => $minrange,
            'maxrange' => $maxrange
        ]);
    }
}
