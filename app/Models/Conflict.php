<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conflict extends Model
{
    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }

    public function faction1()
    {
        return $this->belongsTo('App\Models\Faction', 'faction1_id');
    }

    public function faction2()
    {
        return $this->belongsTo('App\Models\Faction', 'faction2_id');
    }

    public function asset1()
    {
        return $this->morphTo();
    }

    public function asset2()
    {
        return $this->morphTo();
    }

}
