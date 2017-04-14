<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\Influences;

class MapController extends Controller
{
    
    public function index() {
        $systems = System::with('phase')->get();

        
        
        return view('map/index', [
            'systems' => $systems
        ]);
    }

    
}
