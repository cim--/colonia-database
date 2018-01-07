<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function() {
            $this->call(PhaseSeeder::class);
            $this->call(EconomySeeder::class);
            $this->call(GovernmentSeeder::class);
            $this->call(StateSeeder::class);
            $this->call(StationclassSeeder::class);
            $this->call(FacilitySeeder::class);
            $this->call(SystemSeeder::class);
            $this->call(FactionSeeder::class);
            $this->call(StationSeeder::class);
            $this->call(OutfittingSeeder::class);
            // random for testing
            // $this->call(InfluenceTestSeeder::class);
            
            // separate from SystemSeeder for faster testing
            $this->call(EDSMSeeder::class);
        });
    }
}
