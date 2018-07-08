<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function installationclass() {
        return $this->belongsTo('App\Models\Installationclass');
    }
}
