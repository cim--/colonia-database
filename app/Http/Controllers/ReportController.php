<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Systemreport;
use App\Models\Influence;

class ReportController extends Controller
{
    
    public function index(Request $request) {
        // preserves old URL, no longer directly linked
        return view('reports/index');
    }

    public function traffic() {
        return $this->dailyReport('traffic', "Traffic is the number of hyperspace entrances to the system in 24 hours");
    }

    public function crimes() {
        return $this->dailyReport('crime', "Crime is the number of bounties collected in the system in 24 hours");
    }

    public function bounties() {
        return $this->dailyReport('bounties', "Bounties is the number of bounties handed in to stations in the system in 24 hours. In systems with an Interstellar Factor, these bounties may not belong to any in-system faction.");
    }
    
    private function dailyReport($sort, $desc) {
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
            'chart' => $chart,
            'desc' => $desc
        ]);
    }


    public function reach() {

        $reaches = \DB::select('SELECT f.name, FLOOR(SUM(i.influence/100 * s.population)) AS reach FROM factions f INNER JOIN influences i ON (f.id = i.faction_id) INNER JOIN systems s ON (s.id = i.system_id) WHERE i.current = 1 GROUP BY f.name ORDER BY reach DESC');
        
        $dataset = [];
        $labels = [];
        $colours = [];
        foreach ($reaches as $reach) {
            $labels[] = $reach->name;
            $dataset[] = $reach->reach;
            $colours[] = '#'.substr(md5($reach->name), 0, 6);
        }
        
        $chart = app()->chartjs
            ->name("reportchart")
            ->type("horizontalBar")
            ->size(["height" => 20*(count($reaches)+1), "width"=>1000])
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

        $desc = "Reach is the number of people supporting a faction. It is calculated as the sum of system population multiplied by influence percentage for each system a faction is in. For example, a faction present in a system with 100,000 population at 70% influence, and a system with 30,000 population at 20% influence, would have a total reach of <code>(100,000 * 0.7) + (30,000 * 0.2) = 76,000</code>. The (small) additional reach of Colonia Council due to systems on the highway is not included.";
        
        return view('reports/report', [
            'report' => "Reach",
            'chart' => $chart,
            'desc' => $desc
        ]);
    }
    
}
