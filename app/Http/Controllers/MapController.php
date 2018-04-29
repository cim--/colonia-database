<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\Facility;
use App\Models\Influences;

class MapController extends Controller
{
    
    public function index(Request $request) {
        $systems = System::with('phase', 'stations', 'stations.faction', 'facilities')->orderBy('name')->orderBy('catalogue')->get();

        return view('map/index', [
            'systems' => $systems,
            'factions' => Faction::orderBy('name')->notHidden()->get(),
            'facilities' => Facility::where('type', 'System')->orderBy('name')->get()
        ]);
    }

    
}
