<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\Influences;

class DistancesController extends Controller
{
    
    const EXPANSION_LIMIT = 30;
    const MISSION_LIMIT = 15;
    
    public function index() {
        $systems = System::with('phase', 'stations', 'stations.faction')->get();
        $systems = $systems->sortBy(function ($s) {
            return $s->phase->sequence.":".$s->displayName();
        });

        $fakefaction = new Faction; // don't save this!
        $fakefaction->name = "?";
        $fakefaction->id = -1;

        $maxphase = 0;
        foreach ($systems as $idx => $system) {
            if ($system->inhabited() && $system->phase->sequence > $maxphase) {
                $maxphase = $system->phase->sequence;
            }
        }

        $presents = []; // cache
        
        $grid = [];
        foreach ($systems as $idx => $system) {
            $line = [];
            if ($system->inhabited()) {
                $faction = $system->controllingFaction();
            } else {
                $faction = $fakefaction;
            }

            foreach ($systems as $idx2 => $system2) {
                if ($idx == $idx2) {
                    $line[$system2->id] = [
                        'distance' => 0,
                        'present' => true,
                        'full' => false,
                        'available' => false,
                        'candidate' => false,
                        'target' => false
                    ];
                } else {
                    if (!isset($presents[$idx2])) {
                        $presents[$idx2] = $this->currentFactions($system2);
                    }
                    
                    $details = [
                        'distance' => $system->distanceTo($system2),
                        'present' => isset($presents[$idx2][$faction->id]),
                        'full' => count($presents[$idx2]) > 7 || $system2->name == "Colonia" || $system2->catalogue == "Eol Prou LW-L c8-76",
                        'available' => ($system2->phase->sequence <= $maxphase) || ($system->phase->sequence >= $system2->phase->sequence),
                        'candidate' => false,
                        'target' => false
                    ];
                    $line[$system2->id] = $details;
                }
            }
            
            uasort($line, function($a, $b) {
                return $a['distance'] - $b['distance'];
            });
            $targets = 0;
            foreach ($line as $key => $properties) {
                if (!$properties['present']) {
                    if ($properties['distance'] > self::EXPANSION_LIMIT) {
                        break; // that's it...
                    }
                    if ($properties['full'] || !$properties['available']) {
                        // can't expand right now but might later?
                        $line[$key]['candidate'] = true;
                    } else {
                        $line[$key]['candidate'] = true;
                        $line[$key]['target'] = true;
                        $targets++;
                        if ($targets == 3) {
                            break; // enough
                        }
                    }
                }
            }
            
            $grid[$system->id] = $line;
        }

        

        return view('distances/index', [
            'systems' => $systems,
            'grid' => $grid,
            'expansion' => self::EXPANSION_LIMIT,
            'missions' => self::MISSION_LIMIT,
        ]);
    }


    private function currentFactions(System $system) {
        $influences = $system->latestFactions();
        $factions = [];
        foreach ($influences as $influence) {
            $factions[$influence->faction->id] = $influence->faction;
        }
        return $factions;
    }
    
}
