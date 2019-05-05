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

    public function conflicts() {
        return $this->hasMany('App\Models\Conflict');
    }

    public function systemreports() {
        return $this->hasMany('App\Models\Systemreport');
    }

    public function eddnevents() {
        return $this->hasMany('App\Models\Eddnevent');
    }
    
    public function facilities() {
        return $this->belongsToMany('App\Models\Facility')->withPivot('enabled');
    }

    public function history() {
        return $this->morphMany('App\Models\History', 'location');
    }

    public function megashiproutes() {
        return $this->hasMany('App\Models\Megashiproute');
    }

    public function installations() {
        return $this->hasMany('App\Models\Installation');
    }

    public function sites() {
        return $this->hasMany('App\Models\Site');
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

    public function expansionCube(System $other, $radius=20) {
        if (
            (abs($this->x - $other->x) <= $radius) &&
            (abs($this->y - $other->y) <= $radius) &&
            (abs($this->z - $other->z) <= $radius)
        ) {
            return true;
        }
        return false;
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
                    ->with('faction', 'states')->orderBy('influence', 'desc')
                    ->get();
    }

    // optimised for distances page
    public function latestFactionsWithoutEagerLoad() {
        return $this->influences()->where('current', 1)
                    ->orderBy('influence', 'desc')
                    ->get();
    }

    public function factions(Carbon $date) {
        return $this->influences()->whereDate('date', $date->format("Y-m-d"))
                    ->with('faction', 'states')->orderBy('influence', 'desc')
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
                       ->where('estimated', false)
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
            if ($target->bgslock) {
                continue; // locked systems
            }
            if ($faction->currentInfluence($target) !== null) {
                continue;
            }
            if (!$this->expansionCube($target, 40)) {
                continue;
            }
            $tcount = $target->latestFactions()->count();
            if ($tcount == 7) {
                $aggressivecandidates[] = $target;
            } else if ($tcount < 7) {
                $peacefulcandidates[] = $target;
            }
            // else no expansion possible if > 7
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

    public function economySize() {
        $size = $this->stations->sum('economysize');
        $len = strlen($size);
        $places = $len-3;
        $power = 10**$places;
        return round($size/$power)*$power;
    }
}
