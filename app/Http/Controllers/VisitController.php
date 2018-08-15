<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\Station;
use App\Models\Installation;
use App\Models\Megaship;
use App\Models\Site;

class VisitController extends Controller
{
    public function index() {
        $systems = System::count();
        $stations = Station::count();
        $factions = Faction::count();
        $installations = Installation::count();
        $megaships = Megaship::count();
        $sites = Site::count();

        return view('visit.index', [
            'systems' => $systems,
            'stations' => $stations,
            'factions' => $factions,
            'installations' => $installations,
            'megaships' => $megaships,
            'sites' => $sites
        ]);
    }
}
