<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Megashiproute extends Model
{
    public function megaship()
    {
        return $this->belongsTo('App\Models\Megaship');
    }

    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }
}
