<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\Stationclass;
use App\Models\Faction;
use App\Models\Economy;
use App\Models\System;
use App\Models\Facility;
use App\Models\History;
use Illuminate\Http\Request;

class StationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stations = Station::with('system', 'economy', 'stationclass', 'faction', 'faction.government', 'facilities')->get();
        //
        return view('stations/index', [
            'stations' => $stations
        ]);
        //
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

        $classes = Stationclass::orderBy('name')->get();
        $factions = Faction::orderBy('name')->get();
        $economies = Economy::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();
        
        return view('stations/create', [
            'stationFacilities' => Facility::stationFacilities(),
            'classes' => \App\Util::selectMap($classes),
            'factions' => \App\Util::selectMap($factions),
            'economies' => \App\Util::selectMap($economies),
            'systems' => \App\Util::selectMap($systems, false, 'displayName')
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

        $this->validate($request, [
            'name' => 'required',
            'planet' => 'required',
            'distance' => 'required|numeric|min:1'
        ]);

        $station = new Station();
        return $this->updateModel($request, $station);
    }

    private function updateModel(Request $request, Station $station)
    {
        $oldfaction = $station->faction_id;
        
        $station->name = $request->input('name');
        $station->system_id = $request->input('system_id');
        $station->planet = $request->input('planet');
        $station->distance = $request->input('distance');
        $station->stationclass_id = $request->input('stationclass_id');
        $station->economy_id = $request->input('economy_id');
        $station->faction_id = $request->input('faction_id');
        $station->primary = $request->input('primary', 0);
        $station->eddb = $request->input('eddb');
        if ($request->input('gravity') !== "") {
            $station->gravity = $request->input('gravity');
        } else {
            $station->gravity = null;
        }
        $station->save();

        $states = $request->input('state');
        $facs = $request->input('facility',[]);
        $facsettings = [];
        foreach ($facs as $fac) {
            if ($states[$fac] == 1) {
                $facsettings[$fac] = ['enabled' => 1];
            } else {
                $facsettings[$fac] = ['enabled' => 0];
            }
        }
        
        $station->facilities()->sync($facsettings);

        if ($oldfaction && $oldfaction != $station->faction_id) {
            $tick = \App\Util::tick();
            // station has changed ownership
            $loss = new History;
            $loss->location_id = $station->id;
            $loss->location_type = 'App\Models\Station';
            $loss->faction_id = $oldfaction;
            $loss->date = $tick;
            $loss->expansion = false;
            $loss->description = 'lost control of';
            $loss->save();

            $gain = new History;
            $gain->location_id = $station->id;
            $gain->location_type = 'App\Models\Station';
            $gain->faction_id = $station->faction_id;
            $gain->date = $tick;
            $gain->expansion = true;
            $gain->description = 'took control of';
            $gain->save();
        }
        
        return redirect()->route('stations.show', $station->id);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function show(Station $station)
    {
        return view('stations/show', [
            'station' => $station
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function edit(Station $station)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $classes = Stationclass::orderBy('name')->get();
        $factions = Faction::orderBy('name')->get();
        $economies = Economy::orderBy('name')->get();
        $systems = System::where('population', '>', 0)->orderBy('name')->get();
        
        return view('stations/edit', [
            'station' => $station,
            'stationFacilities' => Facility::stationFacilities(),
            'classes' => \App\Util::selectMap($classes),
            'factions' => \App\Util::selectMap($factions),
            'economies' => \App\Util::selectMap($economies),
            'systems' => \App\Util::selectMap($systems, false, 'displayName')
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Station $station)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $this->validate($request, [
            'name' => 'required',
            'planet' => 'required',
            'distance' => 'required|numeric|min:1'
        ]);

        return $this->updateModel($request, $station);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Station  $station
     * @return \Illuminate\Http\Response
     */
    public function destroy(Station $station)
    {
        //
    }
}
