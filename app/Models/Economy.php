<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Economy extends Model
{
    public function systems() {
        return $this->hasMany('App\Models\System');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }
}
