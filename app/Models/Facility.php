<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    public function stations() {
        return $this->belongsToMany('App\Models\Station');
    }

    public function systems() {
        return $this->belongsToMany('App\Models\System');
    }

//
}
