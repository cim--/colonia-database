<?php

use Illuminate\Database\Seeder;

class PhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $phases = ["Founder", "Core", "CEI 1", "CEI 2", "CEI 3", "CEI 4", "CEI 5"];
        foreach ($phases as $idx => $phase) {
            $obj = new App\Models\Phase;
            $obj->name = $phase;
            $obj->sequence = $idx;
            $obj->save();
        }
    }
}
