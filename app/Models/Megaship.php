<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Megaship extends Model
{
    public function megashipclass()
    {
        return $this->belongsTo('App\Models\Megashipclass');
    }

    public function megashiproute()
    {
        return $this->hasMany('App\Models\Megashiproute');
    }
}
