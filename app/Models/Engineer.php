<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Engineer extends Model
{
    public function station()
    {
        return $this->belongsTo('App\Models\Station');
    }

    public function faction()
    {
        return $this->belongsTo('App\Models\Faction');
    }

    public function blueprints()
    {
        return $this->hasMany('App\Models\Blueprint');
    }

    public function displayName()
    {
        return $this->name;
    }
}
