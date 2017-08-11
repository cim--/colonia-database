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

    public function facilities() {
        return $this->belongsToMany('App\Models\Facility')->withPivot('enabled');
    }

    public function enabledFacilities() {
        return $this->belongsToMany('App\Models\Facility')->wherePivot('enabled', 1);
    }

    
    public function history() {
        return $this->morphMany('App\Models\History', 'location');
    }

//
}
