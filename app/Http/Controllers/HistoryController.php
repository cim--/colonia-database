<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\History;

class HistoryController extends Controller
{
    
    public function index() {

        $history = History::with('system', 'system.economy', 'faction', 'faction.government')->orderBy('date', 'desc')->get();
        
        return view('history/index', [
            'historys' => $history
        ]);
    }

    
}
