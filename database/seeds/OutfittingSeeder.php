<?php

use Illuminate\Database\Seeder;

use App\Models\Module;
use App\Models\Moduletype;

class OutfittingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = "database/seeds/outfitting.csv"; // https://github.com/EDCD/FDevIDs/blob/master/outfitting.csv
        $data = file($csv);
        foreach ($data as $idx => $line) {
            if ($idx == 0) { continue; } // skip header
            trim($line);
            list ($discard, $eddn, $category, $description, $mount, $guidance, $ship, $size, $rating, $entitlement) = explode(",", $line);
            if ($entitlement == "powerplay") { continue; } // ignore
            
            switch ($category) {
            case "standard":
                if (strpos($eddn, "_Armour_")) {
                    $mtype = "armour";
                } else {
                    $mtype = "core";
                }
                break;
            case "utility":
            case "hardpoint":
                $mtype = $category;
            break;
            case "internal":
                if (
                    strpos($eddn, "_StellarBodyDiscoveryScanner_") ||
                    strpos($eddn, "_DetailedSurfaceScanner_") ||
                    strpos($eddn, "_DockingComputer_") ||
                    strpos($eddn, "_UnkVesselResearch")
                ) {
                    $mtype = "optionalns";
                } else {
                    $mtype = "optional";
                }
                break;
            default:
                dd("Unknown category ". $category);
            }

            switch ($mtype) {
            case "armour":
                $mtdesc = $description;
                $misize = null;
                $mitype = $ship;
                break;
            case "core":
            case "optional":
            case "utility":
                $mtdesc = $description;
                $misize = $size;
                $mitype = $rating;
                break;
            case "hardpoint":
                if ($guidance != "") {
                    $mtdesc = $guidance." ".$description;
                } else {
                    $mtdesc = $description;
                }
                $misize = $size;
                $mitype = $mount;
                break;
            case "optionalns":
                $mtdesc = $description;
                $misize = null;
                $mitype = null;
                break;
            }

            $moduletype = Moduletype::firstOrCreate([
                'description' => $mtdesc,
                'type' => $mtype
            ]);

            $module = new Module;
            $module->moduletype_id = $moduletype->id;
            $module->eddn = $eddn;
            $module->size = $misize;
            $module->type = $mitype;
            $module->save();
        }

        
    }
}
