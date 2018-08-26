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
    
    public function form()
    {
        $commodities = Commodity::orderBy('description')->whereHas('reserves', function($q) {
            $q->where('reserves', '>', 0);
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

        $confstr = base64_encode(json_encode($config));
        return redirect()->route('logistics.report', $confstr);
    }

    public function report($config)
    {
        $config = json_decode(base64_decode($config));

        $destination = Station::find((int)$config[self::STATION]);
        $volume = (int)$config[self::VOLUME];
        $duration = (int)$config[self::DURATION];
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
        
        $stations = Station::orderBy('name')->tradable()->with('system', 'baselinestocks', 'faction', 'stationclass')->get();
        foreach ($stations as $station) {
            $distance = $station->system->distanceTo($system);
            foreach ($commodities as $commodity) {
                $cbaseline = $station->baselinestocks->where('commodity_id', $commodity->id)->first();
                if ($cbaseline && $cbaseline->reserves > 0) {
                    $current = $station->reserves()->where('current', true)->where('commodity_id', $commodity->id)->first();
                    if ($current) {

                        $option = [
                            'station' => $station,
                            'commodity' => $commodity,
                            'distance' => $distance,
                            'baseline' => $cbaseline,
                            'reserves' => $current,
                            'faction' => $station->faction,
                            'state' => current($station->faction->currentStates()),
                            'pending' => $station->faction->states,
                            'pendinglockdown' => false,
                            'pendingbetter' => false,
                            'pendingworse' => false,
                            'fullness' => 0.5,
                            'regen' => 0
                        ];

                        $total += $current->reserves;
                        
                        $supplyeffect = $effects[$commodity->id]->where('state_id', $option['state']->id)->first();
                        if ($supplyeffect) {
                            $option['sbaseline'] = $supplyeffect->supplysize * $option['baseline']->reserves;

                        
                            $option['supplysize'] = $supplyeffect->supplysize;
                            $option['fullness'] = $option['reserves']->reserves / $option['sbaseline'];
                            foreach ($option['pending'] as $state) {
                                if ($state->name == "Lockdown") {
                                    $option['pendinglockdown'] = true;
                                } else {
                                    $pendingeffect = $effects[$commodity->id]->where('state_id', $state->id)->first();
                                    if ($pendingeffect) {
                                        if ($pendingeffect->supplysize > $supplyeffect->supplysize) {
                                            $option['pendingbetter'] = true;
                                        }
                                        else if ($pendingeffect->supplysize < $supplyeffect->supplysize) {
                                            $option['pendingworse'] = true;
                                        }
                                    }
                                }
                            }
                        } else {
                            $option['sbaseline'] = $option['baseline']->reserves;
                        }
                        
                        if ($commodity->supplycycle > 0) {
                            $option['regen'] = $option['sbaseline']/($commodity->supplycycle/86400);
                            $restock += $option['regen'];
                        }


                        // recommendations
                        if ($option['reserves']->created_at->diffInDays() > 1) {
                            $option['recommendation'] = "Data is old - recheck";
                            $option['score'] = 7;
                        } else if ($option['state']->name == "Lockdown") {
                            $option['recommendation'] = "Lockdown - no hauling possible";
                            $option['score'] = 0;
                        } else if ($option['pendinglockdown']) {
                            $option['recommendation'] = "Pending lockdown - hauling urgent";
                            $option['score'] = 6;
                        } else if ($commodity->supplycycle > 0 && $option['fullness'] > 1-(1/$commodity->supplycycle)) {
                            $option['recommendation'] = "Stock is full - haul to avoid wasting production";
                            $option['score'] = 5;
                        } else if ($option['pendingbetter']) {
                            $option['recommendation'] = "Better BGS state pending - wait to allow it to take effect";
                            $option['score'] = 1;
                        } else if ($option['pendingworse']) {
                            $option['recommendation'] = "Worse BGS state pending - haul before it takes effect";
                            $option['score'] = 4;
                        } else if ($option['supplysize'] >= 1) {
                            $option['recommendation'] = "Good BGS state - haul if practical";
                            $option['score'] = 3;
                        } else {
                            $option['recommendation'] = "Bad BGS state - wait if practical";
                            $option['score'] = 2;
                        }

                        $options[] = $option;
                    }
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
