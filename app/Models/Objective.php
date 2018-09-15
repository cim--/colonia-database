<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{

    public function contributions() {
        return $this->hasMany('App\Models\Contribution');
    }

    public function project() {
        return $this->belongsTo('App\Models\Project');
    }

}
