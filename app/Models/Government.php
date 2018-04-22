<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Government extends Model
{
    public function factions() {
        return $this->hasMany('App\Models\Faction');
    }

    public function regions()
    {
        return $this->belongsToMany('App\Models\Region')->withPivot('frequency');
    }
}
