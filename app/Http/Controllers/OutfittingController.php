<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Station;
use App\Models\System;
use App\Models\Module;
use App\Models\Moduletype;
use App\Models\Ship;

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
            }])->with('blueprints')->orderBy('description')->get();

        $optionalmodules = Moduletype::where('type', 'optional')->whereHas('modules.stations')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
            }])->with('blueprints')->orderBy('description')->get();

        $optionalnsmodules = Moduletype::where('type', 'optionalns')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->with('blueprints')->orderBy('description')->get();
        
        $armours = Moduletype::where('type', 'armour')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
        }])->with('blueprints')->orderBy('description')->get();
        $ships = Module::whereHas('moduletype', function($q) {
            $q->where('type', 'armour');
        })->with('moduletype', 'moduletype.blueprints')->get();
        $shiptypes = [];
        foreach ($ships as $ship) {
            $shiptypes[$ship->type] = $ship->type;
        }
        ksort($shiptypes);

        $weapons = Moduletype::where('type', 'hardpoint')->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->with('blueprints')->orderBy('description')->get();

        $utilities = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->with('blueprints')->orderBy('description')->get();
        
        $utilitiesns = Moduletype::where('type', 'utility')->whereHas('modules', function($q) {
            $q->whereNotIn('type', ['A','B','C','D','E']);
        })->with(['modules' => function($q) use ($reqcurrent) {
                $q->isAvailable($reqcurrent);
                $q->withCount('stations');
            }])->with('blueprints')->orderBy('description')->get();

        
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


    public function moduletype(Moduletype $moduletype, Request $request)
    {
        $modules = Module::where('moduletype_id', $moduletype->id)->with(['stations' => function($q) {
            $q->orderBy('name');
            }])->with('moduletype', 'moduletype.blueprints', 'moduletype.blueprints.engineer')->orderBy('size')->orderBy('type')->get();

        if ($modules->count() == 1) {
            return $this->module($moduletype, $modules->first(), $request);
        }  else {
            return view('outfitting/moduletype', [
                'moduletype' => $moduletype,
                'modules' => $modules
            ]);
        }
    }

    public function module(Moduletype $moduletype, Module $module, Request $request)
    {
        $modules = Module::where('moduletype_id', $moduletype->id)
            ->with(['stations' => function($q) {
                    $q->orderBy('name');
                }])
            ->with('stations.stationclass', 'stations.system', 'moduletype', 'moduletype.blueprints', 'moduletype.blueprints.engineer')
            ->orderBy('size')->orderBy('type')->get();

        $reference = null;
        if ($refid = $request->input('reference', false)) {
            $reference = System::find($refid);
        }
        if ($reference === null) {
            // no or bad reference
            $reference = System::where('name', 'Colonia')->first();
        }
        $systems = System::orderBy('name')->orderBy('catalogue')->populated()->get();
        return view('outfitting/module', [
            'moduletype' => $moduletype,
            'module' => $module,
            'singular' => $modules->count() == 1,
            'reference' => $reference,
            'systems' => $systems
        ]);
    }


    public function shipyard()
    {
        $ships = Ship::withCount("stations")->orderBy('name')->get();
        return view('outfitting/shipyard', [
            'ships' => $ships
        ]);
    }

    public function ship(Ship $ship)
    {
        return view('outfitting/ship', [
            'ship' => $ship
        ]);
    }

}
