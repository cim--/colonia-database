<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;

use App\Models\Economy;
use App\Models\Region;
use App\Models\Government;

class RegionalComparison extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:regionalcomparison {--cached}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Use EDDB data to make comparisons with other regions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $data = [
        "Pleiades" => [
            "sphere" => [-81,-149,-343,100], // Maia
            "allegiance" => null
        ],
        "Coalsack" => [
            "sphere" => [432, 2, 288, 100], // Musca Dark Region PJ-P b6-1
            "allegiance" => null
        ],
        "Witch Head" => [
            "sphere" => [360, -386, -718, 100], // HIP 23759
            "allegiance" => null
        ],
        "California Nebula" => [
            "sphere" => [-303,-236,-860,200], // HIP 18077
            "allegiance" => null
        ],
        "Sol" => [
            "sphere" => [0,0,0,500], // Sol
            "allegiance" => null
        ],
        /* This entry is ignored in the end, it's just to subtract
         * them from the Deep Space numbers */
        "Colonia" => [
            "sphere" => [-9530, -910, 19808, 1000], // Colonia
            "allegiance" => null
        ],
        "Deep Space" => [
            "sphere" => [0,0,0,80000], // Sol... checked in order so overlap is fine
            "allegiance" => null
        ],
        "Federal Systems" => [
            "sphere" => null,
            "allegiance" => "Federation"
        ],
        "Imperial Systems" => [
            "sphere" => null,
            "allegiance" => "Empire"
        ],
        "Alliance Systems" => [
            "sphere" => null,
            "allegiance" => "Alliance"
        ],
    ];
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            \DB::transaction(function() {
                if (!$this->option('cached')) {
                    $this->retrieveFiles();
                }
                $this->initialiseData();
                $this->processSystems();
                $this->processStations();
                $this->processFactions();
                $this->processCommodities();
                $this->report();
            });
        } catch (\Throwable $e) {
            print($e->getTraceAsString());
            throw($e);
        }
    }

    private function retrieveFiles() {
        system("wget -q -O/tmp/systems_populated.jsonl https://eddb.io/archive/v6/systems_populated.jsonl");
        system("wget -q -O/tmp/stations.jsonl https://eddb.io/archive/v6/stations.jsonl");
        system("wget -q -O/tmp/factions.jsonl https://eddb.io/archive/v6/factions.jsonl");
        system("wget -q -O/tmp/listings.csv https://eddb.io/archive/v6/listings.csv");
    }

    private function initialiseData() {
        foreach ($this->data as $key => $region) {
            $region['systems'] = [];
            $region['stations'] = [];
            $region['factories'] = [];
            $region['factions'] = [];
            $region['population'] = 0;
            $region['stock'] = 0;
            $region['demand'] = 0;
            $region['economies'] = $this->initialiseEconomy();
            $region['stationeconomies'] = $this->initialiseEconomy();
            $region['factoryeconomies'] = $this->initialiseEconomy();
            $region['governments'] = $this->initialiseGovernment();
            $this->data[$key] = $region;
        }
    }

    private function initialiseEconomy() {
        $economies = Economy::all();
        $return = [];
        foreach ($economies as $economy) {
            $return[$economy->name] = 0;
        }
        return $return;
    }

    private function initialiseGovernment() {
        $governments = Government::all();
        $return = [];
        foreach ($governments as $government) {
            $return[$government->name] = 0;
        }
        return $return;
    }
    
    private function processSystems() {
        $file = fopen("/tmp/systems_populated.jsonl","r");
        while ($line = fgets($file, 16384)) {
            $sysinfo = json_decode($line);

            if ($sysinfo->population == 0) {
                continue;
            }
            
            $key = $this->getRegion($sysinfo);
            if ($key) {
                $this->data[$key]['systems'][$sysinfo->id] = true;
                $this->data[$key]['population'] += $sysinfo->population;
                if ($sysinfo->primary_economy && $sysinfo->primary_economy != "None") {
                    $econ = $this->ecoName($sysinfo->primary_economy);
                    if (isset($this->data[$key]['economies'][$econ])) {
                        $this->data[$key]['economies'][$econ]++;
                    }
                }
                foreach ($sysinfo->minor_faction_presences as $faction) {
                    $this->data[$key]['factions'][$faction->minor_faction_id] = true;
                }
            }

            $key2 = $this->getAllegiance($sysinfo);
            if ($key2) {
                $this->data[$key2]['systems'][$sysinfo->id] = true;
                $this->data[$key2]['population'] += $sysinfo->population;
                if ($sysinfo->primary_economy && $sysinfo->primary_economy != "None") {
                    $econ = $this->ecoName($sysinfo->primary_economy);
                    if (isset($this->data[$key]['economies'][$econ])) {

                        $this->data[$key2]['economies'][$econ]++;
                    }
                }
                // count factions later
            }
            
        }
        fclose($file);
    }

    private function processStations() {
        $file = fopen("/tmp/stations.jsonl","r");
        while ($line = fgets($file, 16384)) {
            $statinfo = json_decode($line);
            if ($statinfo->economies == ["Private Enterprise"]) {
                continue;
            }

            $storeblock = "stations";
            $statblock = "stationeconomies";
            if ($statinfo->type == "Odyssey Settlement") {
                $storeblock = "factories";
                $statblock = "factoryeconomies";
            }
            
            foreach ($this->data as $key => $region) {

                if ($region['sphere'] && isset($region['systems'][$statinfo->system_id])) {
                    $this->data[$key][$storeblock][$statinfo->id] = true;

                    if ($statinfo->economies) {
                        $secos = count($statinfo->economies);
                        foreach ($statinfo->economies as $seco) {
                            if ($seco != "None") {
                                $econ = $this->ecoName($seco);
                                if (isset($this->data[$key][$statblock][$econ])) {
                                    $this->data[$key][$statblock][$econ] += 1/$secos;
                                }
                            }
                        }
                    }
                } else if ($region['allegiance'] !== null && $region['allegiance'] == $statinfo->allegiance) {
                    $this->data[$key][$storeblock][$statinfo->id] = true;

                    if ($statinfo->economies) {
                        $secos = count($statinfo->economies);
                        foreach ($statinfo->economies as $seco) {
                            $econ = $this->ecoName($seco);
                            if (isset($this->data[$key][$statblock][$econ])) {
                                $this->data[$key][$statblock][$econ] += 1/$secos;
                            }
                        }
                    }
                }
            }

        }
        fclose($file);
    }

    private function processFactions() {
        $file = fopen("/tmp/factions.jsonl","r");
        while ($line = fgets($file, 16384)) {
            $facinfo = json_decode($line);
            
            foreach ($this->data as $key => $region) {
                if ($region['allegiance'] == $facinfo->allegiance && $region['allegiance'] !== null) {
                    $this->data[$key]['factions'][$facinfo->id] = true;
                }

                if (isset($this->data[$key]['factions'][$facinfo->id])) {
                    if (isset($this->data[$key]['governments'][$this->govName($facinfo->government)])) {
                        $this->data[$key]['governments'][$this->govName($facinfo->government)]++;
                    }
                }

                
            }

        }
        fclose($file);
    }

    private function processCommodities() {
        $file = fopen("/tmp/listings.csv","r");
        while ($line = fgetcsv($file, 16384)) {
            $station = $line[1];
            $stock = $line[3];
            $demand = $line[7];
            foreach ($this->data as $key => $region) {
                if (isset($region['stations'][$station]) || isset($region['factories'][$station])) {
                    $this->data[$key]['stock'] += $stock;
                    $this->data[$key]['demand'] += $demand;
                }
            }
            
        }
        fclose($file);
    }
    
    private function report() {
        foreach ($this->data as $key => $region) {
            if ($key != "Colonia") {
                $report = Region::firstOrNew(['name' => $key]);
                $report->systems = count($region['systems']);
                $report->stations = count($region['stations']);
                $report->factories = count($region['factories']);
                $report->factions = count($region['factions']);
                $report->population = $region['population'];
                $report->stock = $region['stock'];
                $report->demand = $region['demand'];

                $report->save();

                foreach ($region['economies'] as $ename => $frequency) {
                    $economy = Economy::where('name', $ename)->first();
                    if ($economy) {
                        $report->economies()->detach($economy->id);
                        $report->economies()->attach($economy->id, ['frequency' => $frequency, 'stationfrequency' => $region['stationeconomies'][$ename], 'factoryfrequency' => $region['factoryeconomies'][$ename]]);
                    }
                }
                foreach ($region['governments'] as $gname => $frequency) {
                    $government = Government::where('name', $gname)->first();
                    if ($government) {
                        $report->governments()->detach($government->id);
                        $report->governments()->attach($government->id, ['frequency' => $frequency]);
                    }
                }
            }
        }
    }
    
    private function ecoName($e) {
        switch ($e) {
        case "Agriculture":
            return "Agricultural";
        case "High Tech":
            return "High-Tech";
        default:
            return $e;
        }
    }

    private function govName($g) {
        switch ($g) {
        case "Communism":
            return "Communist";
        case "Prison":
            return "Detention Centre";
        default:
            return $g;
        }
    }
    
    private function getRegion($sysinfo) {
        foreach ($this->data as $key => $region) {
            if ($region['sphere'] !== null) {
                $x = $region['sphere'][0] - $sysinfo->x;
                $y = $region['sphere'][1] - $sysinfo->y;
                $z = $region['sphere'][2] - $sysinfo->z;
                $r = $region['sphere'][3];
                if ($x*$x+$y*$y+$z*$z <= $r*$r) {
                    return $key;
                }
            }
        }
        return null;
    }

    private function getAllegiance($sysinfo) {
        foreach ($this->data as $key => $region) {
            if ($region['allegiance'] == $sysinfo->allegiance && $region['allegiance'] !== null) {
                return $key;
            }
        }
        return null;
    }
}
