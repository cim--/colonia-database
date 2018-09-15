<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Objective;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::with('objectives', 'objectives.contributions')->orderBy('complete')->orderBy('priority')->get();

        return view('projects/index', [
            'projects' => $projects
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
        return view('projects/create');
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

        $project = new Project;
        $this->saveProject($request, $project, true);
        return redirect()->route('projects.edit', $project->id);
    }


    private function saveProject(Request $request, Project $project, $new=false)
    {
        foreach (['code', 'summary', 'description', 'url', 'priority'] as $field) {
            $project->$field = $request->input($field);
        }
        $project->complete = $request->input('complete', 0);
        $project->save();
        if ($new) { return; }
        
        foreach ($project->objectives as $objective) {
            $code = $request->input('code'.$objective->id, null);
            if ($code === null) {
                $objective->delete();
            } else {
                $label = $request->input('label'.$objective->id);
                $target = $request->input('target'.$objective->id);

                $objective->code = $code;
                $objective->label = $label;
                $objective->target = $target;
                $objective->save();
            }
        }
        $code = $request->input('code0', null);
        if ($code !== null) {
            $label = $request->input('label0');
            $target = $request->input('target0');
            $objective = new Objective;
            $objective->project_id = $project->id;
            $objective->code = $code;
            $objective->label = $label;
            $objective->target = $target;
            $objective->save();
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return view('projects.show', [
            'project' => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        return view('projects/edit', [
            'project' => $project
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
        $this->saveProject($request, $project);
        return redirect()->route('projects.edit', $project->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        $user = \Auth::user();
        if ($user->rank < 2) {
            \App::abort(403);
        }
    }
}
