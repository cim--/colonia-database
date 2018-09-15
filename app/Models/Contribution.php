<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    public function objective() {
        return $this->belongsTo('App\Models\Objective');
    }

}
