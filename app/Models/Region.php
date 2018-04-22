<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['name'];
    
    public function economies()
    {
        return $this->belongsToMany('App\Models\Economy')->withPivot('frequency');
    }

    public function governments()
    {
        return $this->belongsToMany('App\Models\Government')->withPivot('frequency');
    }

}
