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
        $systems = System::get()->sortBy(function($v,$k) { return $v->displayName(); });
        $stations = Station::get()->sortBy(function($v,$k) { return $v->displayName(); });
        $factions = Faction::notHidden()->get()->sortBy(function($v,$k) { return $v->displayName(); });;
        $installations = Installation::get()->sortBy(function($v,$k) { return $v->displayName(); });;
        $megaships = Megaship::get()->sortBy(function($v,$k) { return $v->displayName(); });;
        $sites = Site::get()->sortBy(function($v,$k) { return $v->displayName(); });
        
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
