<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\State;
use App\Models\Reserve;
use App\Models\Station;

class Economy extends Model
{

    /* Date of last significant change to pricing structures */
    private $lastglobal = "2018-03-01";
    
    public function systems() {
        return $this->hasMany('App\Models\System');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }

    public function tradebalances() {
        return $this->hasMany('App\Models\Tradebalance');
    }

    public function regions()
    {
        return $this->belongsToMany('App\Models\Region')->withPivot('frequency');
    }


    
    public function tradeRatio(State $state) {
        $supply = Reserve::where('state_id', $state->id)->where('reserves', '>', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->where('date', '>', $this->lastglobal)->sum('reserves');
        $demand = Reserve::where('state_id', $state->id)->where('reserves', '<', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->where('date', '>', $this->lastglobal)->sum('reserves');
        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }

    public function tradePriceRatio(State $state) {
        $supply = Reserve::where('state_id', $state->id)->where('reserves', '>', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->where('date', '>', $this->lastglobal)->sum(\DB::raw('reserves * price'));
        $demand = Reserve::where('state_id', $state->id)->where('reserves', '<', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->where('date', '>', $this->lastglobal)->sum(\DB::raw('reserves * price'));
        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }
}
