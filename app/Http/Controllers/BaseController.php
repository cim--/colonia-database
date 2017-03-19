<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;

class BaseController extends Controller
{
    public function index() {
        $systems = System::with('phase', 'economy')->orderBy('name')->get();
        $factions = Faction::with('government')->orderBy('name')->get();

        return view('index', [
            'systems' => $systems,
            'factions' => $factions,
        ]);
    }
//
}
