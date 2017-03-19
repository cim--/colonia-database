<?php

use Illuminate\Database\Seeder;

class StationclassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classes = [
            ["Outpost", false, true, true, false],
            ["Coriolis", false, true, true, true],
            ["Orbis", false, true, true, true],
            ["Planetary Outpost", true, true, true, true]
        ];
        foreach ($classes as $class) {
            $obj = new App\Models\Stationclass;
            list($obj->name, $obj->orbital, $obj->hasSmall, $obj->hasMedium, $obj->hasLarge) = $class;
            $obj->save();
        }
    }
}
