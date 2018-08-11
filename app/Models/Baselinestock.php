<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Baselinestock extends Model
{
    public function commodity()
    {
        return $this->belongsTo('App\Models\Commodity');
    }

    public function station()
    {
        return $this->belongsTo('App\Models\Station');
    }
}
