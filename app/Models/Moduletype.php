<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Moduletype extends Model
{
    protected $fillable = ['description', 'type'];

    public function modules()
    {
        return $this->hasMany('App\Models\Modules');
    }
}
