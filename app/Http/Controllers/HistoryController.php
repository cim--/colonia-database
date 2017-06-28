<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\System;
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
    
}
