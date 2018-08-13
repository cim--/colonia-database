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

    public function commoditystat()
    {
        return $this->hasOne('App\Models\Commoditystat');
    }
    
    public function baselinestocks()
    {
        return $this->hasMany('App\Models\Baselinestock');
    }
    
    public function effects()
    {
        return $this->hasMany('App\Models\Effect');
    }
    
    public function displayName()
    {
        if ($this->description) {
            return $this->description;
        }
        return preg_replace("/([a-z])([A-Z])/",'$1 $2',$this->name);
    }

    public function effectForStateID($stateid)
    {
        return $this->effects->where('state_id', $stateid)->first();
    }
}
