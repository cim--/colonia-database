<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Eddnevent;

class TrafficEstimates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:trafficestimates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set estimated traffic levels where practical';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $yesterday;
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->yesterday = new Carbon('yesterday');

        try {
            \DB::transaction(function() {
                
                $systems = System::whereDoesntHave('systemreports', function($q) {
                    $q->whereDate('date', $this->yesterday);
                })->get();
                foreach ($systems as $system) {
                    $this->estimateTrafficLevel($system);
                }
            });
        } catch (\Throwable $e) {
            print($e->getTraceAsString());
            throw($e);
        }
    }

    private function estimateTrafficLevel(System $system) {
        $this->info($system->name);

        $recent = Systemreport::whereNotNull('eddncount')->where('system_id', $system->id)->where('estimated', false)->orderBy('date', 'desc')->take(10)->get();
        $ratios = [];
        foreach ($recent as $report) {
            if ($report->traffic > 0 && $report->eddncount > 0) {
                $ratios[] = $report->traffic / $report->eddncount;
            }
        }
        if (count($ratios) < 5) {
            // insufficient data to get an average
            return;
        }
        $ratios = collect($ratios);

        $this->line("Average: ".$ratios->median());

        $eddncount = Eddnevent::where('system_id', $system->id)->whereDate('eventtime', $this->yesterday)->count();

        $this->line("EDDN Count: ".$eddncount);
        if ($eddncount == 0) {
            // can't guess from here
            return;
        }
        
        $estimate = $eddncount * $ratios->median();

        $lastreport = Systemreport::where('system_id', $system->id)->where('current', 1)->first();
        if ($lastreport) {
            $cratio = $estimate / $lastreport->traffic;
            
            $this->line("Estimate: ".$estimate);
            $newreport = Systemreport::file(
                $system,
                floor($estimate),
                floor($lastreport->bounties * $cratio),
                floor($lastreport->crime * $cratio),
                'estimate',
                true
            );
            
        }
    }
    
}
