<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Station;
use App\Models\Faction;

class HistoryController extends Controller
{
    
    public function index() {

        $history = History::with('location', 'location.economy', 'faction', 'faction.government')->orderBy('date', 'desc')->get();
        
        return view('history/index', [
            'historys' => $history
        ]);
    }

    public function create() {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        
        $factions = Faction::orderBy('name')->get();
        $systems = System::orderBy('catalogue')->where('population', '>', 0)->get()->sort('\App\Util::systemSort');
        $stations = Station::orderBy('name')->get();

        return view('history/create', [
            'factions' => \App\Util::selectMap($factions),
            'systems' => \App\Util::selectMap($systems, false, 'displayName'),
            'stations' => \App\Util::selectMap($stations)
        ]);
    }

    public function store(Request $request) {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $history = new History;
        $history->date = new Carbon($request->input('date'));
        $history->faction_id = $request->input('faction');
        $history->expansion = false;
        $history->description = $request->input('description');
        if ($request->input('station')) {
            $history->location_type = 'App\Models\Station';
            $history->location_id = $request->input('station');
        } else {
            $history->location_type = 'App\Models\System';
            $history->location_id = $request->input('system');
        }
        $history->save();

        return redirect()->route('history');
    }


    public function trends(Request $request) {
        $reports = Systemreport::orderBy('date');

        $minrange = Carbon::parse($request->input('minrange', '3303-03-01'));
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
        
        $reports->whereDate('date', '>=', $minrange)
            ->whereDate('date', '<', $maxrangecomp);
        
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
            'avgtraffic' => [
                'label' => "Traffic (28 day average)",
                'backgroundColor' => 'transparent',
                'borderColor' => '#6060c0',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'ships',
                'pointStyle' => 'cross',
            ],
            'avgcrime' => [
                'label' => "Crime (28 day average)",
                'backgroundColor' => 'transparent',
                'borderColor' => '#c06060',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'credits',
                'pointStyle' => 'cross',
            ],
            'avgbounties' => [
                'label' => "Bounties (28 day average)",
                'backgroundColor' => 'transparent',
                'borderColor' => '#60c060',
                'fill' => false,
                'data' => [],
                'yAxisID' => 'credits',
                'pointStyle' => 'cross',
            ],
        ];
        $latest = [];
        $date = null;

        $averages = [
            'traffic' => [],
            'crimes' => [],
            'bounties' => [],
        ];
        $finalisedate = function(&$datasets, &$averages, $date, $latest) {
            $traffic = 0; $crime = 0; $bounties = 0;
            foreach ($latest as $entry) {
                $traffic += $entry->traffic;
                $crime += $entry->crime;
                $bounties += $entry->bounties;
            }
            foreach (['traffic', 'crime', 'bounties'] as $prop) {
                $datasets[$prop]['data'][] = [
                    'x' => \App\Util::graphDisplayDate($date),
                    'y' => $$prop
                ];
                $averages[$prop][] = $$prop;
                if (count($averages[$prop]) == 28) {
                    $datasets['avg'.$prop]['data'][] = [
                        'x' => \App\Util::graphDisplayDate($date),
                        'y' => floor(array_sum($averages[$prop])/28)
                    ];
                    array_shift($averages[$prop]);
                }
            }
        };
        
        foreach ($reports->cursor() as $report) {
            if ($report->date != $date) {
                if ($date != null) {
                    $finalisedate($datasets, $averages, $date, $latest);
                }
                $date = $report->date;
            }
            $latest[$report->system_id] = $report;
        }
        $finalisedate($datasets, $averages, $date, $latest); // do the last one
        rsort($datasets);
        $chart = app()->chartjs
            ->name("reporthistory")
            ->type("line")
            ->size(["height" => 400, "width"=>1000])
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
                ],
            ]);

        return view('history/trends', [
            'chart' => $chart,
            'minrange' => $minrange,
            'maxrange' => $maxrange,
        ]);
    }
    
}
