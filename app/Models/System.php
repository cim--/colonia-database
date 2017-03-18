<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    public function phase() {
        return $this->belongsTo('App\Models\Phase');
    }

    public function economy() {
        return $this->belongsTo('App\Models\Economy');
    }

    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }
//
}
