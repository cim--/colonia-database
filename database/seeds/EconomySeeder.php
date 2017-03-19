<?php

use Illuminate\Database\Seeder;

use App\Models\Economy;

class EconomySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $economies = [
            "Tourism",
            "Extraction",
            "Agricultural",
            "Refinery",
            "Industrial",
            "Service",
            "High-Tech",
            "Military",
            "Colony",
            "Terraforming"
        ];
        foreach ($economies as $economy) {
            $obj = new Economy;
            $obj->name = $economy;
            $obj->icon = "icons/economies/".strtolower($economy);
            $obj->save();
        }
        //
    }
}
