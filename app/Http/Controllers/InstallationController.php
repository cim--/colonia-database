<?php

namespace App\Http\Controllers;

use App\Models\Installation;
use App\Models\Installationclass;
use App\Models\System;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InstallationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $installations = Installation::with('system', 'system.economy', 'installationclass')->get();

        return view('installations.index', [
            'installations' => $installations
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

        $systems = System::orderBy('name')->get();
        $classes = Installationclass::orderBy('name')->get();

        return view('installations/create', [
            'classes' => \App\Util::selectMap($classes),
            'systems' => \App\Util::selectMap($systems, false, 'displayName'),
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
        $installation = new Installation;
        return $this->save($installation, $request);
    }

    private function save(Installation $installation, Request $request)
    {
        $this->validate($request, [
            'planet' => 'required'
        ]);
        $installation->installationclass_id = $request->input('installationclass_id');
        $installation->system_id = $request->input('system_id');
        $installation->planet = $request->input('planet');
        $installation->name = $request->input('name');
        $installation->satellites = $request->input('satellites', false);
        $installation->trespasszone = $request->input('trespasszone', false);
        $installation->cargo = $request->input('cargo');
        $installation->constructed = Carbon::parse($request->input('constructed'));
        $installation->save();
        return redirect()->route('installations.show', $installation->id);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Installation  $installation
     * @return \Illuminate\Http\Response
     */
    public function show(Installation $installation)
    {
        return view('installations.show', ['installation' => $installation]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Installation  $installation
     * @return \Illuminate\Http\Response
     */
    public function edit(Installation $installation)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $systems = System::orderBy('name')->get();
        $classes = Installationclass::orderBy('name')->get();

        return view('installations/edit', [
            'classes' => \App\Util::selectMap($classes),
            'systems' => \App\Util::selectMap($systems, false, 'displayName'),
            'installation' => $installation
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Installation  $installation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Installation $installation)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        return $this->save($installation, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Installation  $installation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Installation $installation)
    {
        //
    }
}
