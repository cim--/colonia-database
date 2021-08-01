<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    public function stationclass() {
        return $this->belongsTo('App\Models\Stationclass');
    }

    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function faction() {
        return $this->belongsTo('App\Models\Faction');
    }

    public function economy() {
        return $this->belongsTo('App\Models\Economy');
    }

    public function reserves() {
        return $this->hasMany('App\Models\Reserve');
    }

    public function baselinestocks()
    {
        return $this->hasMany('App\Models\Baselinestock');
    }
    
    public function facilities() {
        return $this->belongsToMany('App\Models\Facility')->withPivot('enabled');
    }

    public function enabledFacilities() {
        return $this->belongsToMany('App\Models\Facility')->wherePivot('enabled', 1);
    }

    public function modules()
    {
        return $this->belongsToMany('App\Models\Module')->withPivot('current', 'unreliable')->withTimestamps();
    }

    public function ships()
    {
        return $this->belongsToMany('App\Models\Ship');
    }

    public function engineers()
    {
        // unlikely to ever have more than one
        return $this->hasMany('App\Models\Engineer');
    }
    
    public function history() {
        return $this->morphMany('App\Models\History', 'location');
    }

    // convenience for history
    public function displayName() {
        return $this->name;
    }

    public function displayType() {
        return "station";
    }

    
    public function displayRoute() {
        return 'stations.show';
    }

    public function isController() {
        return $this->primary;
    }
       
    
//
    public function displayEconomySize() {
        if (!$this->economysize) {
            return 0;
        }
        $len = strlen($this->economysize);
        $places = $len-3;
        $power = 10**$places;
        return round($this->economysize/$power)*$power;
    }
    
    public function scopePresent($q) {
        return $q->where('removed', 0);
    }
    
    public function scopeDockable($q) {
        return $q->whereHas('stationclass', function($s) {
            $s->where('hasSmall', 1)
              ->orWhere('hasMedium', 1)
              ->orWhere('hasLarge', 1);
        });
    }

    public function scopeLargeDockable($q) {
        return $q->whereHas('stationclass', function($s) {
            $s->where('hasLarge', 1);
        });
    }

    public function scopeTradable($tq) {
        return $tq->whereHas('facilities', function($q) {
            $q->where('name', 'Commodities');
        })->dockable();
    }

    public function scopeNotFactory($q) {
        return $q->whereHas('stationclass', function($cq) {
            $cq->where('name', 'NOT LIKE', '%Factory');
        });
    }

    public function scopeFactory($q) {
        return $q->whereHas('stationclass', function($cq) {
            $cq->where('name', 'LIKE', '%Factory');
        });
    }

    
    public function currentStateList()
    {
        if ($this->faction->virtual) {
            return collect([State::where('name', 'None')->first()]);
        } else {
            return $this->faction->currentStateList($this->system);
        }
    }

    public function currentStateID()
    {
        if ($this->faction->virtual) {
            return State::where('name', 'None')->first()->id;
        } else {
            return $this->faction->currentStateID($this->system);
        }
    }

    public function marketStateChange()
    {
        $reserve = $this->reserves()->where('current', 1)->first();
        if (!$reserve) {
            return true; // no market data yet, so must be different
        }
        $fstates = $this->currentStateList();
        $rstates = $reserve->states;

        if ($fstates->count() != $rstates->count()) {
            return true; // different number of states
        }
        foreach ($fstates as $fstate) {
            foreach ($rstates as $rstate) {
                if ($fstate->id == $rstate->id) {
                    // matched, next fstate
                    continue 2;
                }
            }
            return true; // this fstate didn't match
        }
        return false; // all states match
    }

    public static function marketUpdateData()
    {
        return Station::with(['reserves' => function($q) {
            $q->where('current', 1);
        }])->whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('facilities', function($q) {
            $q->where('name', 'Commodities');
        })->notFactory()->present()->with('faction', 'system')->orderBy('name')->get();
    }

    public function changeOwnership(Faction $newfaction)
    {
        $oldfaction = $this->faction;

        $this->faction_id = $newfaction->id;
        $this->save();

        $tick = \App\Util::tick();
        // station has changed ownership
        $loss = new History;
        $loss->location_id = $this->id;
        $loss->location_type = 'App\Models\Station';
        $loss->faction_id = $oldfaction->id;
        $loss->date = $tick;
        $loss->expansion = false;
        $loss->description = 'lost control of';
        $loss->save();

        $gain = new History;
        $gain->location_id = $this->id;
        $gain->location_type = 'App\Models\Station';
        $gain->faction_id = $this->faction_id;
        $gain->date = $tick;
        $gain->expansion = true;
        $gain->description = 'took control of';
        $gain->save();

        /* Governance change can affect outfitting - reset */
        $this->modules()->detach();
        $this->ships()->detach();
    }
}
