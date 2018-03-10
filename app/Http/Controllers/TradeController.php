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
            }, 'reserves.station', 'reserves.station.economy'])
               ->orderBy('name')->get();

        $cdata = [];
        $total = 0;
        $tradetotal = 0;
        $stations = [];
        $oldest = Carbon::now();
        foreach ($commodities as $commodity) {
            $crow = [];
            $crow['id'] = $commodity->id;
            $crow['name'] = $commodity->displayName();

            $stock = 0;
            $demand = 0;
            $bestbuy = null;
            $bestsell = null;
            $bestbuyplace = null;
            $bestsellplace = null;
            $imported = [];
            $exported = [];
            foreach ($commodity->reserves as $reserve) {
                $stations[$reserve->station_id] = true;
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
            $crow['exported'] = $exported;
            $crow['imported'] = $imported;
            $crow['buy'] = $bestbuy;
            $crow['sell'] = $bestsell;
            $crow['buyplace'] = $bestbuyplace;
            $crow['sellplace'] = $bestsellplace;
            if ($bestbuyplace != null) {
                $tradetotal += $stock - $demand;
            }
            
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

    
}
