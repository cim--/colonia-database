<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Faction;
use App\Models\Station;
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
            if (!$value->system || !$value->system->inhabited()) {
                return false; // safety for bad data
            }
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
        $stations = Station::with('economy', 'stationclass')->orderBy('name')->get();

        $statescalc = [];
        $states = [];
        $statecounter = 0;
        $none = null;
        foreach ($factions as $faction) {
            $statescalc[$faction->id] = [];
        }
        foreach ($influences as $influence) {
            if ($influence->state->name != "None") {
                if (!isset($statescalc[$influence->faction_id][$influence->state_id])) {
                    $statescalc[$influence->faction_id][$influence->state_id] = $influence->state;
                }
            } else {
                $none = $influence->state;
            }
        }
        foreach ($statescalc as $faction) {
            if (count($faction) == 0) {
                $faction = [$none];
            }
            foreach ($faction as $state) {
                if (!isset($states[$state->name])) {
                    $states[$state->name] = ['state' => $state, 'count' => 0];
                }
                $states[$state->name]['count']++;
            }
        }
        ksort($states);
        
        $population = System::sum('population');

        $iconmap = [];
        $economies = [];
        foreach ($stations as $station) {
            if ($station->stationclass->hasSmall) {
                if (!isset($economies[$station->economy->name])) {
                    $economies[$station->economy->name] = 0;
                }
                $economies[$station->economy->name]++;
                $iconmap[$station->economy->name] = $station->economy->icon;
            }
        }

        $governments = [];
        foreach ($factions as $faction) {
            if (!isset($governments[$faction->government->name])) {
                $governments[$faction->government->name] = 0;
            }
            $governments[$faction->government->name]++;
            $iconmap[$faction->government->name] = $faction->government->icon;


            
        }
        arsort($governments);

        $avgcoordinates = \App\Util::coloniaCoordinates((object)[
            'x' => System::where('population', '>', 0)->avg('x'),
            'y' => System::where('population', '>', 0)->avg('y'),
            'z' => System::where('population', '>', 0)->avg('z')
        ]);
        $maxdist = 0;
        foreach ($systems as $system) {
            if ($system->population > 0) {
                $ccoords = $system->coloniaCoordinates();
                $dist = \App\Util::distance($ccoords, $avgcoordinates);
                if ($dist > $maxdist) {
                    $maxdist = $dist;
                }
            }
        }
        $coldist = \App\Util::distance($avgcoordinates, (object)['x'=>0,'y'=>0,'z'=>0]);

        $bounties = Systemreport::where('current', 1)->sum('bounties')/1000000;
        $maxtraffic = Systemreport::where('current', 1)->max('traffic');
        $mintraffic = Systemreport::where('current', 1)->min('traffic');

        return view('index', [
            'population' => $population,
            'populated' => $systems->filter(function($v) { return $v->population > 0; })->count(),
            'unpopulated' => $systems->filter(function($v) { return $v->population == 0; })->count(),
            'dockables' => $stations->filter(function($v) { return $v->stationclass->hasSmall; })->count(),
            'players' => $factions->filter(function($v) { return $v->player; })->count(),
            'economies' => $economies,
            'governments' => $governments,
            'states' => $states,
            'systems' => $systems,
            'factions' => $factions,
            'stations' => $stations,
            'historys' => $history,
            'importants' => $important,
            'fakeglobals' => ['Retreat', 'Expansion'],
            'iconmap' => $iconmap,
            'maxdist' => $maxdist,
            'coldist' => $coldist,
            'bounties' => $bounties,
            'maxtraffic' => $maxtraffic,
            'mintraffic' => $mintraffic,
        ]);
    }
//
    public function progress() {
        $user = \Auth::user();
        /*          // now allows anonymous read-only access
        if (!$user) {
            \App::abort(403);
        }

        if ($user->rank == 0) {
            return view('progressno');
            } */

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
            'userrank' => $user ? $user->rank : 0, // TODO: Composer
            'influenceupdate' => $influenceupdate->sort('\App\Util::systemSort'),
            'reportsupdate' => $reportsupdate->sort('\App\Util::systemSort'),
            'pendingupdate' => $pendingupdate,
        ]);
    }
}
