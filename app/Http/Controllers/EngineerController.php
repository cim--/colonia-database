<?php

namespace App\Http\Controllers;

use App\Models\Engineer;
use App\Models\Station;
use App\Models\Faction;
use App\Models\Blueprint;
use App\Models\Moduletype;
use Illuminate\Http\Request;

class EngineerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $engineers = Engineer::with('station', 'station.system', 'station.system.economy', 'blueprints')->orderBy('name')->get();
        return view('engineers/index', [
            'engineers' => $engineers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        

        $stations = Station::whereHas('stationclass', function($q) {
            $q->where('name', 'Engineer Base');
        })->orderBy('name')->get();
        $factions = Faction::whereHas('government', function($q) {
            $q->where('name', 'Engineer');
        })->orderBy('name')->get();
        $moduletypes = Moduletype::orderBy('type')->orderBy('description')->get();
        
        return view('engineers/create', [
            'stations' => \App\Util::selectMap($stations),
            'factions' => \App\Util::selectMap($factions),
            'moduletypes' => $moduletypes,
            'blueprints' => collect([])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $engineer = new Engineer;
        return $this->saveEngineer($request, $engineer);
    }


    private function saveEngineer(Request $request, Engineer $engineer)
    {
        $basefields = ['name','discovery','invitation','access','faction_id','station_id'];
        foreach ($basefields as $field) {
            $engineer->$field = $request->input($field);
        }
        $engineer->save();
        
        $moduletypes = Moduletype::orderBy('type')->orderBy('description')->get();
        foreach ($moduletypes as $mtype) {
            $level = $request->input('blueprint'.$mtype->id);
            if ($level < 1.0) {
                Blueprint::where('engineer_id', $engineer->id)->where('moduletype_id', $mtype->id)->delete();
            } else {
                $blueprint = Blueprint::firstOrNew(['engineer_id'=>$engineer->id,'moduletype_id'=> $mtype->id]);
                $blueprint->level = $level;
                $blueprint->save();
            }
        }
        return redirect()->route('engineers.edit', $engineer->id);
    }
        
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Engineer  $engineer
     * @return \Illuminate\Http\Response
     */
    public function show(Engineer $engineer)
    {
        $engineer->load('blueprints', 'blueprints.moduletype');
        return view('engineers.show', [
            'engineer' => $engineer
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\\Models\\Engineer  $engineer
     * @return \Illuminate\Http\Response
     */
    public function edit(Engineer $engineer)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $stations = Station::whereHas('stationclass', function($q) {
            $q->where('name', 'Engineer Base');
        })->orderBy('name')->get();
        $factions = Faction::whereHas('government', function($q) {
            $q->where('name', 'Engineer');
        })->orderBy('name')->get();
        $moduletypes = Moduletype::orderBy('type')->orderBy('description')->get();
        
        return view('engineers/edit', [
            'engineer' => $engineer,
            'stations' => \App\Util::selectMap($stations),
            'factions' => \App\Util::selectMap($factions),
            'moduletypes' => $moduletypes,
            'blueprints' => $engineer->blueprints
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\\Model\\Engineer  $engineer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Engineer $engineer)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        return $this->saveEngineer($request, $engineer);
    }

}
