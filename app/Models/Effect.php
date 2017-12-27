<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Effect extends Model
{
    public function commodity()
    {
        return $this->belongsTo('App\Models\Commodity');
    }

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }
}
