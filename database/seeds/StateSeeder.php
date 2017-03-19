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
            $obj->save();
        }
    }
}
