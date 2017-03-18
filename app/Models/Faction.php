<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
