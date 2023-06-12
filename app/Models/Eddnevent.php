<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eddnevent extends Model
{
    protected $casts = ['created_at' => 'datetime', 'updated_at' => 'datetime', 'eventtime' => 'datetime'];

    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }
}
