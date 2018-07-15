<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    public function system() {
        return $this->belongsTo('App\Models\System');
    }
}
