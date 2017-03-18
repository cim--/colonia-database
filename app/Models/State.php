<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }
    //
}
