<?php

namespace App\Http\Controllers;

use App\Models\Megaship;
use App\Models\System;
use App\Models\Megashipclass;
use App\Models\Megashiproute;
use Illuminate\Http\Request;

class MegashipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $megaships = Megaship::with('megashipclass', 'megashiproutes')->orderBy('serial')->get();

        return view('megaships/index', [
            'megaships' => $megaships
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

        $classes = Megashipclass::orderBy('name')->get();

        return view('megaships/create', [
            'classes' => \App\Util::selectMap($classes)
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
            'megashipclass_id' => 'required|numeric',
            'serial' => 'required',
            'commissioned' => 'nullable|date',
            'decommissioned' => 'nullable|date',
        ]);
        $megaship = new Megaship;
        $this->saveShip($request, $megaship);
        return redirect()->route('megaships.edit', $megaship->id);
    }

    private function saveShip(Request $request, Megaship $megaship)
    {
        $megaship->megashipclass_id = $request->input('megashipclass_id');
        $megaship->serial = $request->input('serial');
        $megaship->commissioned = $request->input('commissioned');
        $megaship->decommissioned = $request->input('decommissioned');
        $megaship->cargodesc = $request->input('cargodesc');
        $megaship->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\\Models\\Megaship  $megaship
     * @return \Illuminate\Http\Response
     */
    public function show(Megaship $megaship)
    {
        return view('megaships.show', [
            'megaship' => $megaship
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\\Models\\Megaship  $megaship
     * @return \Illuminate\Http\Response
     */
    public function edit(Megaship $megaship)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $systems = System::orderBy('name')->get();
        $classes = Megashipclass::orderBy('name')->get();

        return view('megaships/edit', [
            'megaship' => $megaship,
            'systems' => \App\Util::selectMap($systems, true, 'displayName'),
            'classes' => \App\Util::selectMap($classes)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\\Models\\Megaship  $megaship
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Megaship $megaship)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        $this->validate($request, [
            'megashipclass_id' => 'required|numeric',
            'serial' => 'required',
            'commissioned' => 'nullable|date',
            'decommissioned' => 'nullable|date'
        ]);
        $this->saveShip($request, $megaship);
        $this->saveRoutes($request, $megaship);
        return redirect()->route('megaships.edit', $megaship->id);
    }

    private function saveRoutes(Request $request, Megaship $megaship)
    {
        foreach ($megaship->megashiproutes as $route) {
            $sequence = $request->input('sequence'.$route->id, null);
            if ($sequence === null) {
                $route->delete();
            } else {
                $sysid = $request->input('system'.$route->id);
                
                $route->sequence = $sequence;
                if ($sysid) {
                    $route->system_id = $sysid;
                    $route->systemdesc = null;
                } else {
                    $route->system_id = null;
                    $route->systemdesc = $request->input('systemdesc'.$route->id);
                }
                $route->save();
            }
        }
        $sequence = $request->input('sequence0', null);
        if ($sequence !== null) {
            $sysid = $request->input('system0');
            $route = new Megashiproute;
            $route->megaship_id = $megaship->id;
            $route->sequence = $sequence;
            if ($sysid) {
                $route->system_id = $sysid;
                $route->systemdesc = null;
            } else {
                $route->system_id = null;
                $route->systemdesc = $request->input('systemdesc0');
            }
            $route->save();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\\Models\\Megaship  $megaship
     * @return \Illuminate\Http\Response
     */
    public function destroy(Megaship $megaship)
    {
        //
    }
}
