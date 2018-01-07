<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public function moduletype()
    {
        return $this->belongsTo('App\Models\Moduletype');
    }

    public function stations()
    {
        return $this->belongsToMany('App\Models\Station');
    }
    
    public function displayName()
    {
        return $this->moduletype->description." ".$this->size.$this->type;
    }
}
