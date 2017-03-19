<?php

use Illuminate\Database\Seeder;

use App\Models\System;
use App\Models\Faction;
use App\Models\State;
use App\Models\Influence;
use Carbon\Carbon;

class InfluenceTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $systems = System::get();
        $factions = Faction::get();
        $states = State::get();
        
        foreach ($systems as $system) {
            $left = 100;
            for ($i=rand(3,6); $i>0; $i--) {
                $influence = new Influence;
                $influence->system_id = $system->id;
                $influence->state_id = $states->random()->id;
                $influence->faction_id = $factions->random()->id;
                $influence->date = Carbon::now();
                if ($i == 1) {
                    $influence->influence = $left;
                } else {
                    $influence->influence = rand(1, (int)($left / 2));
                    $left -= $influence->influence;
                }
                $influence->save();
            }
        }

        
    }
}
