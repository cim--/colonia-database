<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Faction extends Model
{
    //
    public function government() {
        return $this->belongsTo('App\Models\Government');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }

    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function latestSystems() {
        $date = new Carbon($this->influences()->max('date'));
        return $this->systems($date);
    }

    public function systems(Carbon $date) {
        return $this->influences()->whereDate('date', $date->format("Y-m-d"))
            ->with('system', 'state')->orderBy('influence', 'desc')->get();
    }
}
