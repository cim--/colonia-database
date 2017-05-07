<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Systemreport;

class ReportController extends Controller
{
    
    public function index(Request $request) {
        $reports = Systemreport::where('current', 1)->with('system')->get();

        $sort = $request->input('type', 'traffic');
        if (!in_array($sort, ['traffic', 'crime', 'bounties'])) {
            $sort = 'traffic'; // fallback
        }
        
        $dataset = [];
        $labels = [];
        $colours = [];
        foreach ($reports->sortByDesc($sort) as $report) {
            $labels[] = $report->system->displayName();
            $dataset[] = $report->$sort;
            $colours[] = '#'.substr(md5($report->system->catalogue), 0, 6);
        }
        
        $chart = app()->chartjs
            ->name("reportchart")
            ->type("horizontalBar")
            ->size(["height" => 20*($reports->count()+1), "width"=>1000])
            ->options([
                "legend" => [
                    "display" => false,
                ]
            ])
            ->labels($labels)
            ->datasets([
                [
                    'backgroundColor' => $colours,
                    'data' => $dataset
                ]
            ]);

        return view('reports/index', [
            'report' => ucfirst($sort),
            'chart' => $chart
        ]);
    }

    
}
