<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\History;
use App\Models\Influence;

class BaseController extends Controller
{
    public function index() {

        $history = History::with('location', 'location.economy', 'faction', 'faction.government')
            ->where('date', '>=', Carbon::yesterday()->format("Y-m-d"))
            ->orderBy('date', 'desc')->get();

        $influences = Influence::with('system', 'system.stations', 'system.economy', 'faction', 'faction.government', 'state')
            ->where('current', 1)
            ->get();
        $important = $influences->filter(function ($value, $key) {
            $states = ['Boom', 'Investment', 'None'];
            // ignore uninteresting states
            if (in_array($value->state->name, $states)) {
                return false;
            }
            $states = ['War', 'Election'];
            if (!in_array($value->state->name, $states)) {
                if ($value->system->controllingFaction()->id != $value->faction->id) {
                    return false; // ignore most states for non-controlling factions
                }
            }
            return true;
        });

        $systems = System::with('phase', 'economy')->orderBy('name')->get();
        $factions = Faction::with('government')->orderBy('name')->get();

        $population = System::sum('population');
        
        return view('index', [
            'population' => $population,
            'systems' => $systems,
            'factions' => $factions,
            'historys' => $history,
            'importants' => $important,
            'fakeglobals' => ['Retreat', 'Expansion'],
        ]);
    }
//
    public function progress() {
        $user = \Auth::user();
        if (!$user) {
            \App::abort(403);
        }

        if ($user->rank == 0) {
            return view('progressno');
        }

        $today = Carbon::now();
        $target = \App\Util::tick();
        $influenceupdate = System::where('population', '>', 0)
            ->whereDoesntHave('influences', function($q) use ($target) {
                $q->where('date', $target->format("Y-m-d 00:00:00"));
            })->orderBy('catalogue')->get();

        $reportsupdate = System::where('population', '>', 0)
            ->whereDoesntHave('systemreports', function($q) use ($today) {
                $q->where('date', $today->format("Y-m-d 00:00:00"));
            })->orderBy('catalogue')->get();

        $pendingupdate = [];
        $factions = Faction::with('states')->orderBy('name')->get();
        foreach ($factions as $faction) {
            if ($faction->states->count() > 0 &&
            $target->isSameDay(new Carbon($faction->states[0]->pivot->date))) {
                // pending states up to date
            } else {
                $pendingupdate[] = $faction;
            }
        }
        
        return view('progress', [
            'target' => $target,
            'today' => $today,
            'userrank' => $user->rank, // TODO: Composer
            'influenceupdate' => $influenceupdate,
            'reportsupdate' => $reportsupdate,
            'pendingupdate' => $pendingupdate,
        ]);
    }
}
