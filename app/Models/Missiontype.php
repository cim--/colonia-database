<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Missiontype extends Model
{
    public function sourceState() {
        return $this->belongsTo('App\Models\State', 'sourceState_id');
    }

    public function destinationState() {
        return $this->belongsTo('App\Models\State', 'destinationState_id');
    }
//
}
