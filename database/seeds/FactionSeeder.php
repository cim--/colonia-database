<?php

use Illuminate\Database\Seeder;

use App\Models\Faction;
use App\Models\Government;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $factions = [
            ["Brian’s Thugs", "Anarchy", 0],
            ["Canonn Deep Space Research", "Cooperative", 1],
            ["Colonia Agricultural Co-operative", "Cooperative", 0],
            ["Colonia Co-operative", "Cooperative", 0],
            ["Colonia Council", "Cooperative", 0],
            ["Colonia Mining Co-operative", "Cooperative", 0],
            ["Colonia Mining Enterprise", "Cooperative", 0],
            ["Colonia Refinery Operations", "Cooperative", 0],
            ["Colonia Research Department", "Cooperative", 0],
            ["Colonia Research Division", "Cooperative", 0],
            ["Colonia Tech Combine", "Cooperative", 0],
            ["Colonists of Aurora", "Cooperative", 1],
            ["Consortium Pioneers", "Cooperative", 0],
            ["Edge Fraternity", "Cooperative", 1],
            ["Ed’s 38", "Cooperative", 1],
            ["Eol Prou Group", "Corporate", 1],
            ["Explorers’ Nation", "Cooperative", 0],
            ["Galcop Colonial Defence Commission", "Confederacy", 1],
            ["Golden Hand Coalition", "Anarchy", 0],
            ["ICU Colonial Corps", "Communist", 1],
            ["Jaques", "Cooperative", 0],
            ["Last Phoenix Vault", "Patronage", 1],
            ["Milanov’s Reavers", "Anarchy", 0],
            ["Mobius Colonial Republic Navy", "Democracy", 1],
            ["No Look Here Gang", "Anarchy", 0],
            ["Pioneers and eXploration", "Democracy", 1],
            ["Privateer’s Alliance Expeditionary Force", "Confederacy", 1],
            ["Radio Sidewinder Galactic", "Theocracy", 1],
            ["Smiling Dingo Crew", "Feudal", 1],
            ["Societas Eruditorum de Civitas Dei", "Dictatorship", 1],
            ["The Baddest Company", "Anarchy", 0],
            ["The Crimson Blade", "Anarchy", 0],
            ["The Fire Twins", "Anarchy", 0],
            ["The Tang Clan", "Anarchy", 0],
            ["The Winged Hussars Colonia Initiative", "Cooperative", 1],
        ];

        foreach ($factions as $faction) {
            $obj = new Faction;
            $obj->name = $faction[0];
            $government = Government::where('name', $faction[1])->first();
            if (!$government) { print $faction[1]; }
            $obj->government_id = $government->id;
            $obj->player = (bool)$faction[2];
            $obj->save();
        }
        //
    }
}
