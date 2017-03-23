<?php

namespace App\Http\Controllers;

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

        $target = \App\Util::tick();
        $influenceupdate = System::where('population', '>', 0)
            ->whereDoesntHave('influences', function($q) use ($target) {
                $q->where('date', $target->format("Y-m-d 00:00:00"));
            })->orderBy('catalogue')->get();



        return view('progress', [
            'target' => $target,
            'userrank' => $user->rank, // TODO: Composer
            'influenceupdate' => $influenceupdate
        ]);
    }
}
