<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\State;
use App\Models\Reserve;
use App\Models\Station;

class Economy extends Model
{
    public function systems() {
        return $this->hasMany('App\Models\System');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }

    public function tradebalances() {
        return $this->hasMany('App\Models\Tradebalance');
    }

    
    public function tradeRatio(State $state) {
        $supply = Reserve::where('state_id', $state->id)->where('reserves', '>', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->sum('reserves');
        $demand = Reserve::where('state_id', $state->id)->where('reserves', '<', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->sum('reserves');
        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }

    public function tradePriceRatio(State $state) {
        $supply = Reserve::where('state_id', $state->id)->where('reserves', '>', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->sum(\DB::raw('reserves * price'));
        $demand = Reserve::where('state_id', $state->id)->where('reserves', '<', 0)->whereHas('station', function ($q) {
            $q->where('economy_id', $this->id);
        })->sum(\DB::raw('reserves * price'));
        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }
}
