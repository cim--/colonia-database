<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Station;

class Ship extends Model
{
    public function stations()
    {
        return $this->belongsToMany('App\Models\Station');
    }
}
