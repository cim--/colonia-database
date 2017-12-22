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
        $commodities = Commodity::with(['reserves' => function($q) {
                $q->where('current', true);
            }, 'reserves.station', 'reserves.station.economy'])
            ->orderBy('name')->get();

        $cdata = [];
        $total = 0;
        $stations = [];
        $oldest = Carbon::now();
        foreach ($commodities as $commodity) {
            $crow = [];
            $crow['name'] = $commodity->name;

            $stock = 0;
            $demand = 0;
            $imported = [];
            $exported = [];
            foreach ($commodity->reserves as $reserve) {
                $stations[$reserve->station_id] = true;
                $total += $reserve->reserves;
                if ($reserve->reserves > 0) {
                    $stock += $reserve->reserves;
                    $exported[$reserve->station->economy->id] = $reserve->station->economy;
                } else {
                    $demand -= $reserve->reserves;
                    $imported[$reserve->station->economy->id] = $reserve->station->economy;
                }
                if ($reserve->date->lt($oldest)) {
                    $oldest = $reserve->date;
                }
            }
            $crow['stock'] = $stock;
            $crow['demand'] = $demand;
            $crow['exported'] = $exported;
            $crow['imported'] = $imported;

            $cdata[] = $crow;
        }

        $totalstations = Station::count();
        
        return view('trade/reserves', [
            'commodities' => $cdata,
            'total' => $total,
            'stations' => count($stations),
            'totalstations' => $totalstations,
            'oldest' => $oldest
        ]);
    }
}
