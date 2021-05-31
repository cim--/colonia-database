<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\System;
use App\Models\Station;
use App\Models\Economy;
use App\Models\Stationclass;
use App\Models\Facility;

class FactoryImporter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:factoryimporter {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import factory stations in bulk';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $file = $this->option('file');
        $lines = file($file);

        $factories = [];
        $errors = false;
        foreach ($lines as $idx => $line) {
            $fields = explode("\t", trim($line));
            if ($fields[0] == "System") {
                continue; // skip header
            }

            $system = System::where('name', $fields[0])->first();
            if (!$system) {
                $errors = true;
                $this->error("System ".$fields[0]." not recognised at line ".$idx);
            } 
            $planet = $fields[1];
            $distance = $fields[2];
            $gravity = $fields[3];
            $name = $fields[4];

            $exists = Station::where('name', $name)->where('system_id', $system->id)->first();
            if ($exists) {
                $errors = true;
                $this->error("Station ".$fields[4]." already imported at line ".$idx);
            }
            
            if ($fields[5] == "Agriculture") {
                $fields[5] = "Agricultural";
            }
            $economy = Economy::where('name', $fields[5])->first();
            if (!$economy) {
                $errors = true;
                $this->error("Economy ".$fields[5]." not recognised at line ".$idx);
            }
            $faction = $this->getFaction($fields[6]);    
            if (!$faction) {
                $errors = true;
                $this->error("Faction ".$fields[6]." not recognised at line ".$idx);
            }
            
            $factories[] = compact("system", "planet", "distance", "gravity", "name", "economy", "faction");
        }
        if ($errors) {
            $this->line("Errors detected. Stopping.");
            return;
        }
        $this->line("No errors detected. Building factory data.");
        \DB::transaction(function() use ($factories) {

            $class = Stationclass::where('name', 'Small Planetary Factory')->first()->id;
            $facilities = Facility::whereIn('name', ['Contacts', 'Commodities', 'Missions', 'Repair', 'Refuel'])->get()->pluck('id');
            
            foreach ($factories as $factory) {
                $station = new Station;
                $station->system_id = $factory['system']->id;
                $station->name = $factory['name'];
                $station->planet = $factory['planet'];
                $station->stationclass_id = $class;
                $station->faction_id = $factory['faction']->id;
                $station->economy_id = $factory['economy']->id;
                $station->primary = false;
                $station->strategic = false;
                $station->removed = false;
                $station->distance = $factory['distance'];
                $station->gravity = $factory['gravity'];
                $station->save();
                $station->facilities()->attach($facilities);
                $this->line("Added ".$station->name);
            }

        });
    }

    private function getFaction($oname) {
        // shortcuts
        switch ($oname) {
        case "SECD":
            $name = "Societas Eruditorum de Civitas Dei";
            break;
        case "ICU":
            $name = "ICU Colonial Corps";
            break;
        case "LPV":
            $name = "Last Phoenix Vault";
            break;
        case "SDC":
            $name = "Smiling Dingo Crew";
            break;
        case "Likedeeler":
            $name = "Likedeeler of Colonia";
            break;
        case "Canonn":
            $name = "Canonn Deep Space Research";
            break;
        case "Council":
            $name = "Colonia Council";
            break;
        case "UCA":
            $name = "Ukraine Colonist Alliance";
            break;
        case "MCRN":
            $name = "Mobius Colonial Republic Navy";
            break;
        case "LGC":
            $name = "LGC - Colonia Cartographers' Guild";
            break;
        case "Col Coop":
            $name = "Colonia Co-operative";
            break;
        case "TFRC":
            $name = "The Fuel Rat Colony";
            break;
        case "INO":
            $name = "INO Research";
            break;
        default:
            $name = $oname;
        }
        return Faction::where('name', $name)->first();
    }
    
}
