<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;

class BaseController extends Controller
{
    public function index() {
        $systems = System::with('phase', 'economy')->orderBy('name')->get();
        $factions = Faction::with('government')->orderBy('name')->get();

        return view('index', [
            'systems' => $systems,
            'factions' => $factions,
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
            ->whereDoesntHave('systemreports', function($q) use ($target) {
                $q->where('date', $target->format("Y-m-d 00:00:00"));
            })->orderBy('catalogue')->get();


        return view('progress', [
            'target' => $target,
            'today' => $today,
            'userrank' => $user->rank, // TODO: Composer
            'influenceupdate' => $influenceupdate,
            'reportsupdate' => $reportsupdate,
        ]);
    }
}
