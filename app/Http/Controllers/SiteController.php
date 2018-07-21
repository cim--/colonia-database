<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\Site;
use App\Models\Sitecategory;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sites = Site::with('system', 'system.economy')->orderBy('summary')->get();
        return view('sites.index', [
            'sites' => $sites,
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
        $categories = Sitecategory::orderBy('name')->get();
        
        return view('sites/create', [
            'systems' => \App\Util::selectMap($systems, false, 'displayName'),
            'categories' => \App\Util::selectMap($categories, false),
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
        $site = new Site;
        return $this->save($site, $request);
    }

    private function save(Site $site, Request $request) {
        $this->validate($request, [
            'planet' => 'required',
            'summary' => 'required'
        ]);
        $site->system_id = $request->input('system_id');
        $site->sitecategory_id = $request->input('sitecategory_id');
        $site->planet = $request->input('planet');
        $site->coordinates = $request->input('coordinates');
        $site->summary = $request->input('summary');
        $site->description = $request->input('description');
        $site->save();
        return redirect()->route('sites.show', $site->id);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function show(Site $site)
    {
        return view('sites.show', ['site' => $site]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function edit(Site $site)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }

        $systems = System::orderBy('name')->get();
        $categories = Sitecategory::orderBy('name')->get();
        
        return view('sites/edit', [
            'systems' => \App\Util::selectMap($systems, false, 'displayName'),
            'categories' => \App\Util::selectMap($categories, false),
            'site' => $site
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Site $site)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        return $this->save($site, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Site  $site
     * @return \Illuminate\Http\Response
     */
    public function destroy(Site $site)
    {
        //
    }
}
