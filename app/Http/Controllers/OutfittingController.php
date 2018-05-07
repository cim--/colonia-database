<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\Module;
use App\Models\Moduletype;

class OutfittingController extends Controller
{
    
    public function index() {
        return $this->outfittingSummary(false);
    }
    
    public function current() {
        return $this->outfittingSummary(true);
    }

    private function outfittingSummary($reqcurrent) {
        $coremodules = Moduletype::where('type', 'core')->whereHas('modules.stations')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
            }])->orderBy('description')->get();

        $optionalmodules = Moduletype::where('type', 'optional')->whereHas('modules.stations')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
            }])->orderBy('description')->get();

        $optionalnsmodules = Moduletype::where('type', 'optionalns')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->orderBy('description')->get();
        
        $armours = Moduletype::where('type', 'armour')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
        }])->orderBy('description')->get();
        $ships = Module::whereHas('moduletype', function($q) {
            $q->where('type', 'armour');
        })->get();
        $shiptypes = [];
        foreach ($ships as $ship) {
            $shiptypes[$ship->type] = $ship->type;
        }
        ksort($shiptypes);

        $weapons = Moduletype::where('type', 'hardpoint')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->orderBy('description')->get();

        $utilities = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->orderBy('description')->get();
        
        $utilitiesns = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereNotIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
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
            'reqcurrent' => $reqcurrent
        ]);
    }


    public function moduletype(Moduletype $moduletype)
    {
        $modules = Module::where('moduletype_id', $moduletype->id)->with(['stations' => function($q) {
            $q->orderBy('name');
        }])->with('moduletype')->orderBy('size')->orderBy('type')->get();

        if ($modules->count() == 1) {
            return view('outfitting/module', [
                'moduletype' => $moduletype,
                'module' => $modules->first(),
                'singular' => true
            ]);
        }  else {
            return view('outfitting/moduletype', [
                'moduletype' => $moduletype,
                'modules' => $modules
            ]);
        }
    }

    public function module(Moduletype $moduletype, Module $module)
    {
        $modules = Module::where('moduletype_id', $moduletype->id)->with(['stations' => function($q) {
            $q->orderBy('name');
        }])->with('moduletype')->orderBy('size')->orderBy('type')->get();

        return view('outfitting/module', [
            'moduletype' => $moduletype,
            'module' => $module,
            'singular' => false
        ]);
    }

}
