<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    protected $fillable = ['name'];
    
    public function reserves()
    {
        return $this->hasMany('App\Models\Reserve');
    }

    public function displayName()
    {
        return preg_replace("/([a-z])([A-Z])/",'$1 $2',$this->name);
    }
}
