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
        // new states stabilised 23 Jan?
        $q->where('date', '>', '2020-01-23');
         // ignore high CG demands
        return $q->where('reserves', '>', -500000);
    }

    public static function epochs() {
        // times of major changes in market behaviour
        // first full day after the change generally best to use
        return [
            '2018-02-28', // 3.0
            '2018-12-12', // 3.3
            '2020-01-15', // 3.6
        ];
    }

    public static function epochs2() {
        // times of market data refreshes after major changes
        return [
            '2018-03-03', // 3.0
            '2018-12-16', // 3.3
            '2020-01-17', // 3.6
        ];
    }
    
    public function scopeCurrent($q)
    {
        return $q->where('current', 1)
            ->whereHas('station', function($q2) {
                $q2->present()->tradable();
            });
    }

    public function stateString()
    {
        return $this->states->sortBy('name')->implode('name', ',');
    }
}
