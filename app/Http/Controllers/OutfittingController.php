<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\Module;
use App\Models\Moduletype;

class OutfittingController extends Controller
{
    
    public function index() {
        $coremodules = Moduletype::where('type', 'core')->whereHas('modules.stations')->with(['modules' => function($q) {
            $q->whereHas('stations');
        }])->orderBy('description')->get();

        $optionalmodules = Moduletype::where('type', 'optional')->whereHas('modules.stations')->with(['modules' => function($q) {
            $q->whereHas('stations');
        }])->orderBy('description')->get();

        $optionalnsmodules = Moduletype::where('type', 'optionalns')->with(['modules' => function($q) {
            $q->withCount('stations');
        }])->orderBy('description')->get();
        
        $armours = Moduletype::where('type', 'armour')->with(['modules' => function($q) {
            $q->whereHas('stations');
        }])->orderBy('description')->get();
        $ships = Module::whereHas('moduletype', function($q) {
            $q->where('type', 'armour');
        })->get();
        $shiptypes = [];
        foreach ($ships as $ship) {
            $shiptypes[$ship->type] = $ship->type;
        }
        ksort($shiptypes);

        $weapons = Moduletype::where('type', 'hardpoint')->with(['modules' => function($q) {
                $q->withCount('stations');
            }])->orderBy('description')->get();

        $utilities = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) {
                $q->withCount('stations');
            }])->orderBy('description')->get();
        
        $utilitiesns = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereNotIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) {
                $q->withCount('stations');
            }])->orderBy('description')->get();

        
        return view('outfitting/index', [
            'coremodules' => $coremodules,
            'optmodules' => $optionalmodules,
            'optnsmodules' => $optionalnsmodules,
            'armours' => $armours,
            'shiptypes' => $shiptypes,
            'weapons' => $weapons,
            'utilities' => $utilities,
            'utilitiesns' => $utilitiesns,
        ]);
    }
}
