<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserve extends Model
{
    protected $dates = ['date'];
    
    public function commodity()
    {
        return $this->belongsTo('App\Models\Commodity');
    }

    public function station()
    {
        return $this->belongsTo('App\Models\Station');
    }

    public function states()
    {
        return $this->belongsToMany('App\Models\State');
    }

    /* Restrict market analysis to normal market types */
    public function scopeNormalMarkets($q)
    {
        // significant market changes in 3.6 for mined goods
        $q->where('date', '>', '2020-01-14');
         // ignore high CG demands
        $q->where('reserves', '>', -100000);
    }

    public function stateString()
    {
        return $this->states->sortBy('name')->implode('name', ',');
    }
}
