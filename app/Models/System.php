<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    public function phase() {
        return $this->belongsTo('App\Models\Phase');
    }

    public function economy() {
        return $this->belongsTo('App\Models\Economy');
    }

    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }


    public function systemreports() {
        return $this->hasMany('App\Models\Systemreport');
    }

    public function facilities() {
        return $this->belongsToMany('App\Models\Facility')->withPivot('enabled');
    }

    public function history() {
        return $this->morphMany('App\Models\History', 'location');
    }
//
    public function scopePopulated($q) {
        return $q->where('population', '>', 0);
    }

    
    public function inhabited() {
        return $this->population > 0;
    }
    
    public function displayName() {
        if ($this->name) {
            return $this->name;
        } else {
            return $this->catalogue;
        }
    }

    public function mainStation() {
        return $this->stations()->where('primary', 1)->first();
    }

    public function coloniaCoordinates() {
        return \App\Util::coloniaCoordinates($this);
    }
    
    public function distanceTo(System $other) {
        $distance = sqrt(
            (($this->x - $other->x)*($this->x - $other->x)) +
            (($this->y - $other->y)*($this->y - $other->y)) +
            (($this->z - $other->z)*($this->z - $other->z))
        );
        return $distance;
    }

    public function refreshEDSM() {
        $name = $this->displayName();
        // temp for names in EDSM
        if ($name == "Canis Subridens") { $name = "Tas"; }
        if ($name == "Aurora Astrum") { $name = $this->catalogue; }
        
        $url = "https://www.edsm.net/api-v1/system?systemName=".
            urlencode($name).
            "&showCoordinates=1&showId=1";
        $json = file_get_contents($url);
        $data = json_decode($json);
        if (isset($data->coords)) {
            $this->x = $data->coords->x;
            $this->y = $data->coords->y;
            $this->z = $data->coords->z;
            $this->edsm = $data->id;
        } else {
            $this->x = $this->y = $this->z = $this->edsm = 0;
        }
    }

    public function controllingFaction()
    {
        $station = $this->stations->where('primary', true)->first();
        if ($station) {
            return $station->faction;
        }
        return null;
    }
    
    public function latestFactions() {
        return $this->influences()->where('current', 1)
                    ->with('faction', 'state')->orderBy('influence', 'desc')
                    ->get();
    }

    public function factions(Carbon $date) {
        return $this->influences()->whereDate('date', $date->format("Y-m-d"))
                    ->with('faction', 'state')->orderBy('influence', 'desc')
                    ->get();
    }

    public function factionsGapproof(Carbon $date) {
        $tick = $date->copy();
        for ($i=1;$i<=7;$i++) {
            $factions = $this->factions($tick);
            if (count($factions) > 0) {
                return $factions;
            }
            $tick->subDay();
        }
        return $factions; // give up after a week
    }

    
    public function latestReport() {
        $report = $this->systemreports()
                       ->where('current', 1)
                       ->first();
        if ($report) {
            return $report;
        }
        $fake = new Systemreport;
        $fake->traffic = 0;
        $fake->crime = 0;
        $fake->bounties = 0;
        return $fake;
        
    }

    public function report(Carbon $date) {
        return $this->systemreports()
                    ->whereDate('date', $date->format("Y-m-d"))
                    ->first();
    }


    public function expansionsFor(Faction $faction = null) {
        
        if ($this->population == 0) {
            return [[],[]];
        }
        if ($faction === null) {
            $faction = $this->controllingFaction();
        }

        $systems = System::all();
        $peacefulcandidates = [];
        $aggressivecandidates = [];
        foreach ($systems as $target) {
            if ($target->id == $this->id) {
                continue;
            }
            if ($target->population == 0) {
                continue;
            }
            if ($target->name == "Ratri" || $target->name == "Ratraii" || $target->name == "Colonia") {
                continue; // locked systems
            }
            if ($faction->currentInfluence($target) !== null) {
                continue;
            }
            if ($target->distanceTo($this) > 30) {
                continue;
            }
            if ($target->latestFactions()->count() >= 7) {
                $aggressivecandidates[] = $target;
            } else {
                $peacefulcandidates[] = $target;
            }
        }
        $sorter = function($a, $b) {
            return $this->sign($a->distanceTo($this)-$b->distanceTo($this));
        };
            
        usort($aggressivecandidates, $sorter);
        usort($peacefulcandidates, $sorter);
        return [$peacefulcandidates, $aggressivecandidates];
    }

    private function sign($a) {
        if($a > 0) { return 1; }
        if($a < 0) { return -1; }
        return 0;
    }

}
