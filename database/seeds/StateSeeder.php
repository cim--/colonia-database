<?php

use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = [
            "None",
            "Boom",
            "Bust",
            "Civil Unrest",
            "Lockdown",
            "War",
            "Election",
            "Expansion",
            "Investment",
            "Retreat",
            "Famine",
            "Outbreak"
        ];
        //
        foreach ($states as $state) {
            $obj = new App\Models\State;
            $obj->name = $state;
            $obj->icon = "icons/states/".strtolower($state);
            if (in_array($state, ["Boom", "Expansion", "Investment"])) {
                $state->sign = 1;
            } else if (in_array($state, ["None", "War", "Election"])) {
                $state->sign = 0;
            } else {
                $state->sign = -1;
            }
            $obj->save();
        }
    }
}
