<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    protected $dates = ['created_at', 'updated_at', 'constructed'];

    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function installationclass() {
        return $this->belongsTo('App\Models\Installationclass');
    }
}
