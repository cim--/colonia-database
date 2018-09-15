<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    public function objectives() {
        return $this->hasMany('App\Models\Objective');
    }


}
