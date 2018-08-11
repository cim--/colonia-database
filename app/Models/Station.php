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
        return $this->belongsToMany('App\Models\Module')->withPivot('current', 'unreliable');
    }

    public function ships()
    {
        return $this->belongsToMany('App\Models\Ship');
    }

    
    public function history() {
        return $this->morphMany('App\Models\History', 'location');
    }

    // convenience for history
    public function displayName() {
        return $this->name;
    }
//

    public function scopeDockable($q) {
        return $q->whereHas('stationclass', function($s) {
            $s->where('hasSmall', 1)
              ->orWhere('hasMedium', 1)
              ->orWhere('hasLarge', 1);
        });
    }


    public function currentState()
    {
        if ($this->faction->virtual) {
            return State::where('name', 'None')->first();
        } else {
            return $this->faction->currentState($this->system);
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

}
