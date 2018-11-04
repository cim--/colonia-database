<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function influences() {
        return $this->belongsToMany('App\Models\Influence');
    }

    public function reserves() {
        return $this->belongsToMany('App\Models\Reserve');
    }

    public function effects()
    {
        return $this->hasMany('App\Models\Effect');
    }
    
    public function tradebalances() {
        return $this->hasMany('App\Models\Tradebalance');
    }

    // pending states
    public function factions() {
        return $this->belongsToMany('App\Models\Faction')->withPivot('date');
    }

}
