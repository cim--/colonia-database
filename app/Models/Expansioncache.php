<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expansioncache extends Model
{
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function target() {
        return $this->belongsTo('App\Models\System', 'target_id');
    }
//
}
