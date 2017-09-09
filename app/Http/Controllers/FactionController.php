<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\State;
use App\Models\System;
use App\Models\Influence;
use App\Models\Government;
use Illuminate\Http\Request;

class FactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $factions = Faction::with('government', 'states')->get();
        //
        return view('factions/index', [
            'factions' => $factions
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

        $governments = Government::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();

        return view('factions/create', [
            'governments' => \App\Util::selectMap($governments),
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

        $this->validate($request, ['name' => 'required']);

        $faction = new Faction();
        return $this->updateModel($request, $faction);
    }

    private function updateModel(Request $request, Faction $faction) {
        $faction->name = $request->input('name');
        $faction->url = $request->input('url');
        $faction->eddb = $request->input('eddb');
        $faction->government_id = $request->input('government_id');
        $faction->player = $request->input('player', 0);
        $faction->system_id = $request->input('system_id');
        $faction->save();

        return redirect()->route('factions.show', $faction->id);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function show(Faction $faction)
    {
        $faction->load('government', 'stations', 'stations.system', 'stations.stationclass', 'stations.economy', 'states');

        $datasets = \App\Util::stateBars($faction);
        sort($datasets); // compact
        $chart = app()->chartjs
            ->name("statetimes")
            ->type("horizontalBar")
            ->size(["height" => 100, "width"=>500])
            ->labels(['States'])
            ->datasets($datasets)
            ->options([
                'scales' => [
                    'yAxes' => [
                        [ 'stacked' => true ]
                    ],
                    'xAxes' => [
                        [ 'stacked' => true ]
                    ],
                ]
            ]);
        
        return view('factions/show', [
            'faction' => $faction,
            'systems' => $faction->latestSystems(),
            'chart' => $chart
        ]);
    }

    public function showHistory(Faction $faction)
    {
        $influences = Influence::where('faction_id', $faction->id)
            ->with('system')
            ->with('state')
            ->orderBy('date')
            ->get();

        $systems = [];
        $dates = [];
        $entries = [];
        $datasets = [];

        $seen = [];
        $lastdate = null;
        
        foreach ($influences as $influence) {
            $date = $influence->date->format("Y-m-d");
            if ($lastdate != $influence->date) {
                if ($lastdate != null) {
                    foreach ($systems as $sid => $system) {
                        if (!isset($seen[$sid])) {
                            $datasets[$sid]['data'][] = [
                                'x' => \App\Util::graphDisplayDate($lastdate),
                                'y' => null
                            ];
                        }
                    }
                    
                }
                $lastdate = $influence->date;
                $seen = [];
            }
            
            $system = $influence->system_id;
            $seen[$system] = 1;
            
            if (!isset($datasets[$influence->system_id])) {
                $datasets[$influence->system_id] = [
                    'label' => $influence->system->displayName(),
                    'backgroundColor' => 'transparent',
                    'borderColor' => '#'.substr(md5($influence->system->catalogue), 0, 6),
                    'fill' => false,
                    'data' => []
                ];
            }
            
            $dates[$date] = 1;
            $systems[$system] = $influence->system;

            $entries[$date][$system] = [$influence->influence, $influence->state];
            $datasets[$influence->system_id]['data'][] = [
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
        
        return view('factions/showhistory', [
            'chart' => $chart,
            'faction' => $faction,
            'history' => $entries,
            'systems' => $systems,
            'dates' => $dates
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function edit(Faction $faction)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        $target = \App\Util::tick();

        $states = State::orderBy('name')->get();
        $pending = $faction->states->sortBy('name');

        $latest = null;

        if ($pending->count() > 0) {
            $latest = new Carbon($pending[0]->pivot->date);
        }

        $governments = Government::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();
        
        return view('factions/edit', [
            'target' => $target,
            'states' => $states,
            'pending' => $pending,
            'faction' => $faction,
            'latest' => $latest,
            'governments' => \App\Util::selectMap($governments),
            'systems' => \App\Util::selectMap($systems, false, 'displayName')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faction $faction)
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
                'name' => 'required',
            ]);

            return $this->updateModel($request, $faction);
        }
        
        $pending = $request->input('pending');
        if (!is_array($pending)) {
            return redirect()->route('factions.edit', $faction->id)->with('status',
            [
                'warning' => 'You must select at least one state (which may be "None")'
            ]);
        }

        $tick = \App\Util::tick();
        $sync = [];
        foreach ($pending as $state) {
            $sync[$state] = ['date' => $tick->format('Y-m-d 00:00:00')];
        }
        
        $faction->states()->sync($sync);

        \Log::info("Pending state update", [
            'faction' => $faction->name,
            'user' => $user->name
        ]);
        
        return redirect()->route('factions.show', $faction->id)->with('status',
        [
            'success' => 'Pending states updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faction $faction)
    {
        //
    }
}
