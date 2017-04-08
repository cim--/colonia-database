<?php

use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stationfacilities = [
            "Commodities",
            "Contacts",
            "Missions",
            "Passengers",
            "Outfitting",
            "Shipyard",
            "Cartographics",
            "Black Market",
            "Repair",
            "Refuel",
            "Restock"
        ];
        $systemfacilities = [
            "Compromised Nav Beacon",
            "Low RES",
            "Normal RES",
            "High RES",
            "Haz RES",
            "Icy Belt Cluster",
            "Rocky Belt Cluster",
            "Metal-Rich Belt Cluster",
            "Metallic Belt Cluster",
            "Icy Rings",
            "Rocky Rings",
            "Metal-Rich Rings",
            "Metallic Rings",
        ];
        //
        foreach ($stationfacilities as $fac) {
            $obj = new App\Models\Facility;
            $obj->name = $fac;
            $obj->icon = "icons/facilities/stations/".strtolower(str_replace(" ", "", $fac));
            $obj->type = "Station";
            $obj->save();
        }
        foreach ($systemfacilities as $fac) {
            $obj = new App\Models\Facility;
            $obj->name = $fac;
            $obj->icon = "icons/facilities/systems/".strtolower(str_replace(" ", "", $fac));
            $obj->type = "System";
            $obj->save();
        }

    }
}
