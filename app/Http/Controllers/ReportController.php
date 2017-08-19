<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Systemreport;

class ReportController extends Controller
{
    
    public function index(Request $request) {
        // preserves old URL, no longer directly linked
        return view('reports/index');
    }

    public function traffic(Request $request) {
        return $this->dailyReport($request, 'traffic');
    }

    public function crimes(Request $request) {
        return $this->dailyReport($request, 'crime');
    }

    public function bounties(Request $request) {
        return $this->dailyReport($request, 'bounties');
    }
    
    private function dailyReport(Request $request, $sort) {
        $reports = Systemreport::where('current', 1)->with('system')->get();
       
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

        return view('reports/report', [
            'report' => ucfirst($sort),
            'chart' => $chart
        ]);
    }

    
}
