<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\System;
use App\Models\Faction;
use App\Models\State;
use App\Models\Influence;
use Illuminate\Http\Request;

class SystemController extends Controller
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
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function show(System $system)
    {
        if (!$system->x) {
            $system->refreshCoordinates();
        }
        
        $system->load('phase', 'economy', 'stations', 'stations.stationclass');
        $others = System::where('id', '!=', $system->id)->get();
        return view('systems/show', [
            'system' => $system,
            'others' => $others,
            'controlling' => $system->controllingFaction(),
            'factions' => $system->latestFactions(),
            'userrank' => \Auth::user() ? \Auth::user()->rank : 0
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function edit(System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }
        if (date("H") < 15) {
            $target = Carbon::yesterday();
        } else {
            $target = Carbon::now();
        }

        $today = $system->factions($target);
        $yesterday = $system->factions($target->copy()->subDay());

        $factions = Faction::orderBy('name')->get();
        $states = State::orderBy('name')->get();

        $factions = \App\Util::selectMap($factions);
        $factions[0] = "(No faction)";
        
        return view('systems/edit', [
            'today' => $today->count() > 0 ? $today : $yesterday,
            'yesterday' => $yesterday,
            'target' => $target,
            'system' => $system,
            'factions' => $factions,
            'states' => \App\Util::selectMap($states),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, System $system)
    {
        $user = \Auth::user();
        if ($user->rank < 1) {
            \App::abort(403);
        }

        if (date("H") < 15) {
            $target = Carbon::yesterday();
        } else {
            $target = Carbon::now();
        }
        $factions = $request->input('faction');
        $influences = $request->input('influence');
        $states = $request->input('state');

        $total = 0;
        $valid = true;
        for($i=0;$i<=7;$i++) {
            if ($factions[$i] != 0) {
                if ($influences[$i] < 1 || $influences[$i] > 99) {
                    $valid = false;
                }
                $total += $influences[$i];
            }
        }
        if ((int)($total+0.5) != 100) {
            $valid = false;
        }
        if (!$valid) {
            return redirect()->route('systems.edit', $system->id)->with('status',
            [
                'warning' => 'Faction influences must add up to 100 and be between 1 and 99 each'
            ]);
        }
        
        Influence::where('system_id', $system->id)
            ->where('date', $target)
            ->delete();
        
        for($i=0;$i<=7;$i++) {
            if ($factions[$i] != 0) {
                $obj = new Influence;
                $obj->system_id = $system->id;
                $obj->faction_id = $factions[$i];
                $obj->state_id = $states[$i];
                $obj->date = $target;
                $obj->influence = $influences[$i];
                $obj->save();
            }
        }
        return redirect()->route('systems.show', $system->id)->with('status',
        [
            'success' => 'Faction influences updated'
        ]);
//
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\System  $system
     * @return \Illuminate\Http\Response
     */
    public function destroy(System $system)
    {
        //
    }
}
