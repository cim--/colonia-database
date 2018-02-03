<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Faction;
use App\Models\State;
use App\Models\Phase;
use App\Models\Economy;
use App\Models\Influence;
use App\Models\Facility;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $systems = System::with('phase', 'economy', 'stations', 'stations.faction', 'stations.faction.government', 'stations.economy', 'facilities')->get();
        //
        
        return view('systems/index', [
            'systems' => $systems
        ]);
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

        $phases = Phase::orderBy('sequence')->get();
        $economies = Economy::orderBy('name')->get();

        return view('systems/create', [
            'systemFacilities' => Facility::systemFacilities(),
            'phases' => \App\Util::selectMap($phases),
            'economies' => \App\Util::selectMap($economies, true)
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
            'catalogue' => 'required',
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'z' => 'required|numeric',
            'population' => 'required|numeric|min:0',
        ]);
        
        $system = new System();
        return $this->updateModel($request, $system);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function show(System $system)
    {
        if (!$system->x) {
            $system->refreshCoordinates();
        }
        
        $system->load('phase', 'economy', 'stations', 'stations.stationclass', 'facilities');
        $others = System::where('id', '!=', $system->id)->with('economy', 'stations', 'stations.faction', 'stations.faction.government')->get();


        $reports = Systemreport::where('system_id', $system->id)->orderBy('date')->get();
        $datasets = [
            'traffic' => [
                'label' => "Traffic",
                'backgroundColor' => 'transparent',
                'borderColor' => '#000090',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'ships',
            ],
            'crime' => [
                'label' => "Crime",
                'backgroundColor' => 'transparent',
                'borderColor' => '#900000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'credits',
            ],
            'bounties' => [
                'label' => "Bounties",
                'backgroundColor' => 'transparent',
                'borderColor' => '#009000',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'credits',
            ],
        ];
        $properties = ['traffic', 'crime', 'bounties'];
        foreach ($reports as $report) {
            foreach ($properties as $prop) {
                $datasets[$prop]['data'][] = [
                    'x' => \App\Util::graphDisplayDate($report->date),
                    'y' => $report->$prop
                ];
            }
        }
        sort($datasets); // compact
        
        $chart = app()->chartjs
            ->name("reporthistory")
            ->type("line")
            ->size(["height" => 400, "width"=>1000])
            ->datasets($datasets)
            ->options(['scales' => [
                    'xAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'bottom',
                            'ticks' => [
                                'callback' => "@@chart_xaxis_callback@@"
                            ]
                        ]
                    ],
                    'yAxes' => [
                        [
                            'id' => 'ships',
                            'gridLines' => [
                                'display' => false
                            ],
                            'scaleLabel' => [
                                'labelString' => "Ships",
                                'display' => true
                            ],
                            'ticks' => [
                                'min' => 0
                            ]
                        ],
                        [
                            'id' => 'credits',
                            'gridLines' => [
                                'display' => false
                            ],
                            'scaleLabel' => [
                                'labelString' => "Credits",
                                'display' => true
                            ],
                            'position' => 'right',
                            'ticks' => [
                                'min' => 0
                            ]
                        ]
                    ]
                ],
                'tooltips' => [
                    'callbacks' => [
                        'title' => "@@tooltip_label_title@@",
                        'label' => "@@tooltip_label_number@@"
                    ]
                ]
            ]);


        
        return view('systems/show', [
            'system' => $system,
            'chart' => $chart,
            'colcoords' => $system->coloniaCoordinates(),
            'others' => $others,
            'controlling' => $system->controllingFaction(),
            'factions' => $system->latestFactions(),
            'report' => $system->latestReport()
        ]);
    }

    public function showHistory(System $system)
    {
        $influences = Influence::where('system_id', $system->id)
            ->with('faction')
            ->with('state')
            ->orderBy('date')
            ->get();

        $factions = [];
        $dates = [];
        $entries = [];

        $datasets = [];

        $lastdate = null;
        
        foreach ($influences as $influence) {
            $date = $influence->date->format("Y-m-d");
            if ($lastdate != $influence->date) {
                if ($lastdate != null) {
                    foreach ($factions as $fid => $faction) {
                        if (!isset($seen[$fid])) {
                            $datasets[$fid]['data'][] = [
                                'x' => \App\Util::graphDisplayDate($lastdate),
                                'y' => null
                            ];
                        }
                    }
                    
                }
                $lastdate = $influence->date;
                $seen = [];
            }
            $faction = $influence->faction_id;

            $seen[$faction] = 1;
            
            if (!isset($datasets[$influence->faction_id])) {
                $datasets[$influence->faction_id] = [
                    'label' => $influence->faction->name,
                    'backgroundColor' => 'transparent',
                    'borderColor' => '#'.substr(md5($influence->faction->name), 0, 6),
                    'fill' => false,
                    'data' => []
                ];
            }
            
            $dates[$date] = 1;
            $factions[$faction] = $influence->faction;

            $entries[$date][$faction] = [$influence->influence, $influence->state];

            $datasets[$influence->faction_id]['data'][] = [
                'x' => \App\Util::graphDisplayDate($influence->date),
                'y' => $influence->influence
            ];
            
        }

        sort($datasets); // compact
        krsort($dates);

        $chart = app()->chartjs
            ->name("influencehistory")
            ->type("line")
            ->size(["height" => 350, "width"=>1000])
            ->datasets($datasets)
            ->options([
                'scales' => [
                    'xAxes' => [
                        [
                            'type' => 'linear',
                            'position' => 'bottom',
                            'ticks' => [
                                'callback' => "@@chart_xaxis_callback@@"
                            ]
                        ]
                    ],
                    'yAxes' => [
                        [
                            'ticks' => [
                                'min' => 0
                            ]
                        ]
                    ]
                ],
                'tooltips' => [
                    'callbacks' => [
                        'title' => "@@tooltip_label_title@@",
                        'label' => "@@tooltip_label_percent@@"
                    ]
                ]
            ]);

        
        return view('systems/showhistory', [
            'chart' => $chart,
            'system' => $system,
            'history' => $entries,
            'factions' => $factions,
            'dates' => $dates
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function edit(System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        $target = \App\Util::tick();

        $today = $system->factions($target);
        $yesterday = $system->factions($target->copy()->subDay());
        if (count($yesterday) == 0) {
            $yesterday = $system->latestFactions();
        }
        
        $factions = Faction::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $factions = \App\Util::selectMap($factions);
        $factions[0] = "(No faction)";

        $phases = Phase::orderBy('sequence')->get();
        $economies = Economy::orderBy('name')->get();
        
        return view('systems/edit', [
            'today' => $today->count() > 0 ? $today : $yesterday,
            'yesterday' => $yesterday,
            'target' => $target,
            'system' => $system,
            'factions' => $factions,
            'states' => \App\Util::selectMap($states),
            'phases' => \App\Util::selectMap($phases),
            'economies' => \App\Util::selectMap($economies, true),
            'systemFacilities' => Facility::systemFacilities()
        ]);
    }

    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function editReport(System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        $target = Carbon::now();

        $latest = $system->latestReport();

        return view('systems/editreport', [
            'latest' => $latest,
            'target' => $target,
            'system' => $system
        ]);
    }

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }

        if ($request->input('editmain', 0) == 1) {
            if ($user->rank < 2) {
                \App::abort(403);
            }
            $this->validate($request, [
                'catalogue' => 'required',
                'x' => 'required|numeric',
                'y' => 'required|numeric',
                'z' => 'required|numeric',
                'population' => 'required|numeric|min:0',
            ]);

            return $this->updateModel($request, $system);
        }
        $target = \App\Util::tick();

        $factions = $request->input('faction');
        $influences = $request->input('influence');
        $states = $request->input('state');

        $total = 0;
        $valid = true;
        for($i=0;$i<=7;$i++) {
            if ($factions[$i] != 0) {
                if ($influences[$i] < 1 || $influences[$i] > 99) {
                    $valid = false;
                }
                $total += $influences[$i];
            }
        }
        if ((int)($total+0.5) != 100) {
            $valid = false;
        }
        if (!$valid) {
            return redirect()->route('systems.edit', $system->id)->with('status',
            [
                'warning' => 'Faction influences must add up to 100 and be between 1 and 99 each'
            ]);
        }
        
        Influence::where('system_id', $system->id)
            ->where('date', $target->format("Y-m-d 00:00:00"))
            ->delete();

        Influence::where('system_id', $system->id)
            ->where('current', true)
            ->update(['current' => false]);
        
        for($i=0;$i<=7;$i++) {
            if ($factions[$i] != 0) {
                $obj = new Influence;
                $obj->system_id = $system->id;
                $obj->faction_id = $factions[$i];
                $obj->state_id = $states[$i];
                $obj->date = $target;
                $obj->influence = $influences[$i];
                $obj->current = true;
                $obj->save();
            }
        }
        \Log::info("Influence update", [
            'system' => $system->displayName(),
            'user' => $user->name
        ]);
        return redirect()->route('systems.show', $system->id)->with('status',
        [
            'success' => 'Faction influences updated'
        ]);
//
    }

    private function updateModel(Request $request, System $system)
    {
        $system->catalogue = $request->input('catalogue');
        $system->name = $request->input('name');
        $system->x = $request->input('x');
        $system->y = $request->input('y');
        $system->z = $request->input('z');
        $system->virtualonly = $request->input('virtualonly', 0);
        $system->bgslock = $request->input('bgslock', 0);
        $system->edsm = $request->input('edsm');
        $system->eddb = $request->input('eddb');
        $system->population = $request->input('population');
        $system->explorationvalue = $request->input('explorationvalue');
        $system->phase_id = $request->input('phase_id');
        $system->economy_id = $request->input('economy_id');
        $system->save();

        $system->facilities()->sync($request->input('facility',[]));

        return redirect()->route('systems.show', $system->id);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function updateReport(Request $request, System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }

        $traffic = $request->input('traffic');
        $crime = $request->input('crime');
        $bounties = $request->input('bounties');

        if (!is_numeric($traffic) || (int)$traffic < 0 ||
        !is_numeric($crime) || (int)$crime < 0 ||
        !is_numeric($bounties) || (int)$bounties < 0) {
            return redirect()->route('systems.editreport', $system->id)->with('status',
            [
                'warning' => 'All reports must be non-negative integers'
            ]);
        }
        
        $today = Carbon::now();

        Systemreport::file($system, $traffic, $bounties, $crime, $user->name);
        
        return redirect()->route('systems.show', $system->id)->with('status',
        [
            'success' => 'Reports updated'
        ]);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function destroy(System $system)
    {
        //
    }
}
