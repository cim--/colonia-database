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
            $this->call(StationclassSeeder::class);
            $this->call(SystemSeeder::class);
            $this->call(FactionSeeder::class);
            $this->call(StationSeeder::class);


            $this->call(EDSMSeeder::class);
        });
    }
}
