<?php

use Illuminate\Database\Seeder;

class EDSMSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $systems = App\Models\System::get();
        foreach ($systems as $system) {
            $system->refreshEDSM();
            $system->save();
        }
    }
}
