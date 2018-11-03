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
        // significant market changes in 3.0 so before then will be inaccurate
        $q->where('date', '>', '2018-03-01');
        // tax break week - don't use for analysis
        $q->where(function ($q2) {
            $q2->where('date', '>=', '2018-06-01')
              ->orWhere('date', '<', '2018-05-24');
        });
        // ignore UA-bombed stations: The Pit (before 1 March so no need)
        // McDonald Platform
        $q->where(function($q2) {
            $q2->where('station_id', '!=', 71)
               ->orWhere('date', '<', '2018-08-22')
               ->orWhere('date', '>', '2018-08-29'); // may need to extend?
        });
        $q->where('reserves', '>', -100000); // ignore high CG demands
    }
}
