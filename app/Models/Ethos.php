<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ethos extends Model
{
    protected $table = 'ethoses';

    public function factions()
    {
        return $this->hasMany('App\Models\Faction');
    }

    public function adminName()
    {
        // for selectMaps
        return $this->adminname;
    }
}
