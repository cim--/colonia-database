<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eddnevent extends Model
{
    protected $dates = ['created_at', 'updated_at', 'eventtime'];

    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }
}
