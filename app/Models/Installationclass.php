<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installationclass extends Model
{
    public function installations() {
        return $this->hasMany('App\Models\Installation');
    }
}
