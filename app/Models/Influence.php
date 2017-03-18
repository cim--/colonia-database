<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Influence extends Model
{
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function faction() {
        return $this->belongsTo('App\Models\Faction');
    }

    public function state() {
        return $this->belongsTo('App\Models\State');
    }

}
