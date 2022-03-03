<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Station;
use App\Models\Commodity;
use App\Models\Effect;

class LogisticsController extends Controller
{
    const STATION = 0;
    const VOLUME = 1;
    const DURATION = 2;
    const COMMODITIES = 3;
    const NONLOCAL = 4;
    
    public function form()
    {
        $commodities = Commodity::orderBy('description')->whereHas('reserves', function($q) {
            $q->where('reserves', '>', 0)->where('current', true);
        })->get();
        $stations = Station::orderBy('name')->tradable()->get();
        
        return view('logistics/form', [
            'commodities' => \App\Util::selectMap($commodities, true, 'displayName'),
            'stations' => \App\Util::selectMap($stations),
        ]);
    }

    public function configure(Request $request)
    {
        $config = [];

        $this->validate($request, [
            'station_id' => 'required|min:1',
            'volume' => 'required|min:1',
            'duration' => 'required|min:1',
            'commodity0' => 'required|min:1'
        ]);
        
        $config[self::STATION] = (int)$request->input('station_id');
        $config[self::VOLUME] = (int)$request->input('volume');
        $config[self::DURATION] = (int)$request->input('duration');
        $config[self::COMMODITIES] = [];
        for ($i=0;5>$i;$i++) {
            $config[self::COMMODITIES][$i] = (int)$request->input('commodity'.$i);
        }
        $config[self::NONLOCAL] = (int)$request->input('nonlocal', false);

        $confstr = base64_encode(json_encode($config));
        return redirect()->route('logistics.report', $confstr);
    }

    public function report($config)
    {
        $config = json_decode(base64_decode($config));

        $destination = Station::find((int)$config[self::STATION]);
        $volume = (int)$config[self::VOLUME];
        $duration = (int)$config[self::DURATION];
        $nonlocal = isset($config[self::NONLOCAL]) ? (bool)$config[self::NONLOCAL] : false; 
        $commodities = [];
        $effects = [];
        foreach ($config[self::COMMODITIES] as $cid) {
            $commodity = Commodity::find($cid);
            if ($commodity) {
                $commodities[] = $commodity;
                $effects[$commodity->id] = Effect::where('commodity_id', $commodity->id)->get();
            }
        }

        $system = $destination->system;

        $options = [];

        $total = 0;
        $restock = 0;
        
        $stationsquery = Station::orderBy('name')->tradable()->with('system', 'baselinestocks', 'faction', 'stationclass');
        if ($nonlocal) {
            $stationsquery->where('system_id', '!=', $system->id);
        }
        $stations = $stationsquery->get();
        foreach ($stations as $station) {
            $distance = $station->system->distanceTo($system);
            $outdated = $station->marketStateChange();
            foreach ($commodities as $commodity) {
                $cbaseline = $station->baselinestocks->where('commodity_id', $commodity->id)->first();
                $current = $station->reserves()->where('current', true)->where('commodity_id', $commodity->id)->first();
                if (($cbaseline && $cbaseline->reserves > 0) || ($current && $current->reserves > 0)) {

                    $option = [
                        'station' => $station,
                        'commodity' => $commodity,
                        'distance' => $distance,
                        'baseline' => $cbaseline,
                        'reserves' => $current,
                        'faction' => $station->faction,
                        'states' => $station->faction->currentStateList($station->system),
                        'fullness' => 0.5,
                        'regen' => 0
                    ];

                    if ($current) {
                        $total += $current->reserves;
                    }

                    $knowneffects = true;
                    $supplyeffects = 1;
                    $haslockdown = false;
                    $haswar = false;
                    foreach ($option['states'] as $state) {
                        $supplyeffect = $effects[$commodity->id]->where('state_id', $state->id)->first();
                        if (!$supplyeffect) {
                            $knowneffects = false;
                        } else {
                            $supplyeffects *= $supplyeffect->supplysize;
                        }
                        if ($state->name == "Lockdown") {
                            $haslockdown = true;
                        }
                        if ($state->name == "War") {
                            $haswar = true;
                        }
                    }
                    if ($cbaseline) {
                        if ($knowneffects) {

                            $option['sbaseline'] = $supplyeffects * $option['baseline']->reserves;
                            if ($option['sbaseline'] == 0) {
                                $option['sbaseline'] = 1;
                            }
                            $option['supplysize'] = $supplyeffects;
                            if ($option['reserves']) {
                                $option['fullness'] = $option['reserves']->reserves / $option['sbaseline'];
                            } else {
                                $option['fullness'] = 0;
                            }

                        } else {
                            $option['sbaseline'] = $option['baseline']->reserves;
                            if ($option['sbaseline'] == 0) {
                                $option['sbaseline'] = 1;
                            }
                            $option['supplysize'] = $option['baseline']->reserves;
                            $option['fullness'] = 1;
                        }
                        
                        if ($commodity->supplycycle > 0) {
                            $option['regen'] = $option['sbaseline']/($commodity->supplycycle/86400);
                            $restock += $option['regen'];
                        }
                    }

                    // recommendations
                    if ($haslockdown) {
                        $option['recommendation'] = "Lockdown - no hauling possible - improve security";
                        $option['score'] = 0;
                    } else if ($haswar && strpos($station->stationclass->name, "Factory")) {
                        $option['recommendation'] = "War preventing landing - end it first";
                        $option['score'] = 0;
                    } else if ($outdated) {
                        $option['recommendation'] = "Data is old - recheck";
                        $option['score'] = 7;
                    } else if (!$current) {
                        $option['recommendation'] = "State preventing production - improve if possible";
                        $option['score'] = 1;
                    } else if ($cbaseline && ($commodity->supplycycle > 0 && $option['fullness'] > 1-(1/$commodity->supplycycle))) {
                        $option['recommendation'] = "Stock is full - haul to avoid wasting production";
                        $option['score'] = 5;
                    } else if ($option['fullness'] < 0.01) {
                        $option['recommendation'] = "Stock is empty - wait for production";
                        $option['score'] = 3;
                    } else if ($knowneffects && $supplyeffects >= 1) {
                        $option['recommendation'] = "Good BGS state - haul if practical";
                        $option['score'] = 4;
                    } else if (!$knowneffects) {
                        $option['recommendation'] = "Insufficient data";
                        $option['score'] = 3;
                    } else {
                        $option['recommendation'] = "Bad BGS state - improve if practical";
                        $option['score'] = 2;
                    }

                    $options[] = $option;
                }
            }
        }

        $options = collect($options);
        $options->sortBy('distance');    

        $bestcase = $total + ($restock * $duration);
        
        return view('logistics/report', [
            'destination' => $destination,
            'volume' => $volume,
            'bestcase' => $bestcase,
            'options' => $options,
            'total' => $total,
            'restock' => $restock,
            'duration' => $duration,
            'commodities' => $commodities
        ]);
    }
}
