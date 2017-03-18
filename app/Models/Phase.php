<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phase extends Model
{
    public function systems() {
        return $this->hasMany('App\Models\System');
    }
    //
}
