<?php

use Illuminate\Database\Seeder;

use App\Models\System;
use App\Models\Station;
use App\Models\Stationclass;
use App\Models\Economy;
use App\Models\Faction;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stations = [
            ["Jaques Station", "Eol Prou RS-T d3-94", "Orbis", "4", "1576", "Jaques"],
            ["Colonia Hub", "Eol Prou RS-T d3-94", "Planetary Outpost", "2 a", "1009", "Colonia Council"],
            ["The Pit", "Eol Prou LW-L c8-133", "Outpost", "2", "18", "Consortium Pioneers"],
            ["Morten’s Paradise", "Eol Prou LW-L c8-138", "Outpost", "1", "426", "Colonia Agricultural Co-operative"],
            ["Malik Station", "Eol Prou LW-L c8-28", "Outpost", "A 1 a", "1013", "Colonia Refinery Operations"],
            ["Dervish Platform", "Eol Prou LW-L c8-306", "Outpost", "A 4 a", "1734", "Colonia Tech Combine"],
            ["Colonia Dream", "Eol Prou LW-L c8-76", "Coriolis", "A 3 a", "26", "Colonia Co-operative"],
            ["Vitto Orbital", "Eol Prou VY-R d4-443", "Outpost", "6", "1093", "Eol Prou Group"],
            ["Balakor’s Research Post", "Eol Prou YD-W b17-1", "Outpost", "4", "126", "Colonia Research Department"],
            ["Diva Mines", "Eol Prou YD-W b17-5", "Outpost", "A 5", "310", "Jaques"],
            ["The Nest", "Eol Prou IW-W e1-1601", "Planetary Outpost", "2 a", "780", "Last Phoenix Vault"],
            ["Dezhnev Landing", "Eol Prou IW-W e1-2400", "Planetary Outpost", "5 a", "433", "Eol Prou Group"],
            ["Arcanonn’s Legacy", "Eol Prou IW-W e1-3167", "Planetary Outpost", "B 2", "30032", "Canonn Deep Space Research"],
            ["Walhalla Port", "Eol Prou IW-W e1-3246", "Planetary Outpost", "C 1", "20283", "Pioneers and eXploration"],
            ["Vera Rubin Complex", "Eol Prou KW-L c8-164", "Planetary Outpost", "A 1 b", "1097", "Galcop Colonial Defence Commission"],
            ["Broadcasting Bay", "Eol Prou LW-L c8-227", "Planetary Outpost", "12 a", "1293", "Radio Sidewinder Galactic"],
            ["Tsiolkovskiy Horizon", "Eol Prou NH-K c9-40", "Planetary Outpost", "A 1", "17", "ICU Colonial Corps"],
            ["Rebolo Port", "Eol Prou PX-T d3-336", "Planetary Outpost", "A 2 a", "2549", "Explorers’ Nation"],
            ["Malcolm Oasis", "Eol Prou PX-T d3-347", "Planetary Outpost", "B 2", "19425", "Societas Eruditorum de Civitas Dei"],
            ["Kolonia Sobieski", "Eol Prou YI-W b17-19", "Planetary Outpost", "A 2 b", "566", "The Winged Hussars Colonia Initiative"],
            ["Dunker’s Rest", "Eol Prou LW-L c8-10", "Planetary Outpost", "3 a", "1079", "Ed’s 38"],
            ["Kremmen’s Respite", "Eol Prou LW-L c8-6", "Planetary Outpost", "8 a", "366", "Privateer’s Alliance Expeditionary Force"],
            ["Pedersen’s Legacy", "Eol Prou LW-L c8-54", "Planetary Outpost", "A 1 a", "20", "Mobius Colonial Republic Navy"],
            ["Prisma Renata", "Eol Prou PX-T d3-1609", "Planetary Outpost", "3 a", "47", "Colonists of Aurora"],
            ["Concordia Hub", "Eol Prou IW-W e1-3231", "Planetary Outpost", "A 10 a", "3687", "Edge Fraternity"],
            ["The Bone Yard", "Eol Prou SS-T d3-241", "Planetary Outpost", "A 3 a", "187", "Smiling Dingo Crew"]
        ];

        foreach ($stations as $station) {
            $obj = new Station;
            $obj->name = $station[0];
            $obj->primary = ($station[0] != "Colonia Hub");
            $system = System::where('catalogue', $station[1])->first();
            $obj->system_id = $system->id;
            $obj->economy_id = ($station[0] != "Colonia Hub") ? $system->economy->id : Economy::where('name', 'Industrial')->first()->id;
            $obj->stationclass_id = Stationclass::where('name', $station[2])->first()->id;
            $obj->planet = $station[3];
            $obj->distance = $station[4];
            $obj->faction_id = Faction::where('name', $station[5])->first()->id;
            $obj->save();
        }
    }
}
