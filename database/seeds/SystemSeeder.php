<?php

use Illuminate\Database\Seeder;
use App\Models\Phase;
use App\Models\System;
use App\Models\Economy;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $systems = [
            [
                "Colonia", "Eol Prou RS-T d3-94", 0, "Tourism", 60000
            ],
            [
                null, "Eol Prou LW-L c8-133", 1, "Extraction", 2000
            ],
            [
                null, "Eol Prou LW-L c8-138", 1, "Extraction", 20000
            ],
            [null, "Eol Prou LW-L c8-28", 1, "Refinery", 3200],
            [null, "Eol Prou LW-L c8-306", 1, "High-Tech", 4500],
            [null, "Eol Prou LW-L c8-76", 1, "Industrial", 35000],
            [null, "Eol Prou VY-R d4-443", 1, "Agricultural", 15000],
            [null, "Eol Prou YD-W b17-1", 1, "Service", 1500],
            [null, "Eol Prou YD-W b17-5", 1, "Service", 1900],
            ["Phoenix", "Eol Prou IW-W e1-1601", 2, "Refinery", 50000],
            ["Meretrida", "Eol Prou IW-W e1-2400", 2, "Industrial", 50000],
            ["Canonnia", "Eol Prou IW-W e1-3167", 2, "High-Tech", 50000],
            ["Magellan", "Eol Prou IW-W e1-3246", 2, "Tourism", 50000],
            ["Garuda", "Eol Prou KW-L c8-164", 2, "Military", 50000],
            ["Signalis", "Eol Prou LW-L c8-227", 2, "Colony", 50000],
            ["Pyrrha", "Eol Prou NH-K c9-40", 2, "High-Tech", 50000],
            ["Union", "Eol Prou PX-T d3-336", 2, "Colony", 50000],
            ["Pergamon", "Eol Prou PX-T d3-347", 2, "Industrial", 50000],
            ["Kopernik", "Eol Prou YI-W b17-19", 2, "Extraction", 50000],
            ["Dubbuennel", "Eol Prou LW-L c8-10", 3, "Industrial", 50000],
            ["Kioti 368", "Eol Prou LW-L c8-6", 3, "Colony", 50000],
            ["Mobia", "Eol Prou LW-L c8-54", 3, "Industrial", 50000],
            ["Aurora Astrum", "Eol Prou PX-T d3-1609", 3, "Extraction", 50000],
            ["Edge Fraternity Landing", "Eol Prou IW-W e1-3231", 3, "Colony", 50000],
            ["Canis Subridens", "Eol Prou SS-T d3-241", 3, "Colony", 50000],
            [null, "Eol Prou IW-W e1-2469", 4, null, null],
            [null, "Eol Prou IW-W e1-533", 4, null, null],
            [null, "Eol Prou LW-L c8-264", 4, null, null],
            [null, "Eol Prou RS-T d3-686", 4, null, null],
            [null, "Eol Prou SS-T d3-147", 4, null, null],
            [null, "Eol Prou LW-L c8-142", 5, null, null],
            [null, "Eol Prou LW-L c8-194", 5, null, null],
            [null, "Eol Prou LW-L c8-1", 5, null, null],
            [null, "Eol Prou DK-U b18-4", 5, null, null],
            [null, "Eol Prou VX-X b16-1", 5, null, null]
        ];
        //
        foreach ($systems as $system) {
            $obj = new System;
            $phase = Phase::where('sequence', $system[2])->first();
            $obj->phase_id = $phase->id;
            $obj->name = $system[0];
            $obj->catalogue = $system[1];
            $obj->population = $system[4] ? $system[4] : 0;
            if ($system[3] != null) {
                $economy = Economy::where('name', $system[3])->first();
                $obj->economy_id = $economy->id;
            }
            $obj->refreshEDSM();
            $obj->save();
        }

        
    }
}
