<?php

namespace App\Http\Controllers;

use App\Models\Missiontype;
use App\Models\State;
use Illuminate\Http\Request;

class MissionController extends Controller
{

    private $magnitudes = [
        -3 => "---",
        -2 => "--",
        -1 => "-",
        0 => "none",
        1 => "+",
        2 => "++",
        3 => "+++"
    ];
    private $positivemagnitudes = [
        0 => "none",
        1 => "+",
        2 => "++",
        3 => "+++"
    ];
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $missions = Missiontype::with('sourceState', 'destinationState')->orderBy('type')->get();

        return view('missions/index', [
            'missions' => $missions
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

        $states = \App\Util::selectMap(State::orderBy('name')->get());
        return view('missions/create', [
            'states' => $states,
            'magnitudes' => $this->magnitudes,
            'positivemagnitudes' => $this->positivemagnitudes,
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
        $type = new Missiontype();
        return $this->updateModel($type, $request);
    }

    private function updateModel(Missiontype $missiontype, Request $request)
    {
        $missiontype->type = $request->input('type');
        $missiontype->reputationMagnitude = $request->input('reputationMagnitude');
        $missiontype->sourceInfluenceMagnitude = $request->input('sourceInfluenceMagnitude');
        $missiontype->sourceState_id = $request->input('sourceState');
        $missiontype->sourceStateMagnitude = $request->input('sourceStateMagnitude');
        if ($request->input('hasDestination', 0)) {
            $missiontype->hasDestination = 1;
            $missiontype->destinationInfluenceMagnitude = $request->input('destinationInfluenceMagnitude');
            $missiontype->destinationState_id = $request->input('destinationState');
            $missiontype->destinationStateMagnitude = $request->input('destinationStateMagnitude');
        } else {
            $missiontype->hasDestination = 0;
            $missiontype->destinationInfluenceMagnitude = null;
            $missiontype->destinationState_id = null;
            $missiontype->destinationStateMagnitude = null;
        }
        $missiontype->save();
        return redirect()->route('missions.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Missiontype  $missiontype
     * @return \Illuminate\Http\Response
     */
    public function show(Missiontype $missiontype)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Missiontype  $missiontype
     * @return \Illuminate\Http\Response
     */
    public function edit(Missiontype $missiontype)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Missiontype  $missiontype
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Missiontype $missiontype)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Missiontype  $missiontype
     * @return \Illuminate\Http\Response
     */
    public function destroy(Missiontype $missiontype)
    {
        //
    }
}
