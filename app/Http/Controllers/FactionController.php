<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\State;
use Illuminate\Http\Request;

class FactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function show(Faction $faction)
    {
        $faction->load('government', 'stations', 'stations.system', 'stations.stationclass', 'stations.economy', 'states');
        return view('factions/show', [
            'faction' => $faction,
            'systems' => $faction->latestSystems(),
            'userrank' => \Auth::user() ? \Auth::user()->rank : 0
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function edit(Faction $faction)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        $target = \App\Util::tick();

        $states = State::orderBy('name')->get();
        $pending = $faction->states->sortBy('name');

        $latest = null;

        if ($pending->count() > 0) {
            $latest = new Carbon($pending[0]->pivot->date);
        }
        
        return view('factions/edit', [
            'target' => $target,
            'states' => $states,
            'pending' => $pending,
            'faction' => $faction,
            'latest' => $latest
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faction $faction)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        $pending = $request->input('pending');
        if (!is_array($pending)) {
            return redirect()->route('factions.edit', $faction->id)->with('status',
            [
                'warning' => 'You must select at least one state (which may be "None")'
            ]);
        }

        $tick = \App\Util::tick();
        $sync = [];
        foreach ($pending as $state) {
            $sync[$state] = ['date' => $tick->format('Y-m-d 00:00:00')];
        }
        
        $faction->states()->sync($sync);
        return redirect()->route('factions.show', $faction->id)->with('status',
        [
            'success' => 'Pending states updated'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faction  $faction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faction $faction)
    {
        //
    }
}
