<?php

use Illuminate\Database\Seeder;

use App\Models\Government;

class GovernmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $governments = [
            "Cooperative",
            "Anarchy",
            "Corporate",
            "Democracy",
            "Confederacy",
            "Communist",
            "Dictatorship",
            "Feudal",
            "Patronage",
            "Theocracy"
        ];
        foreach ($governments as $government) {
            $obj = new Government;
            $obj->name = $government;
            $obj->icon = "icons/governments/".strtolower($government);
            $obj->save();
        }

    }
}
