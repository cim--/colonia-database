<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\State;
use App\Models\Reserve;
use App\Models\Commodity;
use App\Models\Station;

class Economy extends Model
{

    /* Date of last significant change to pricing structures */
    private $lastglobal = "2018-12-12";
    
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
        return $this->belongsToMany('App\Models\Region')->withPivot('frequency', 'stationfrequency', 'factoryfrequency');
    }
    
    public function scopeAnalyse($q) {
        return $q->where('analyse', 1);
    }

    public function scopeBaseline($q) {
        return $q->where('analyse', true)
                 ->orWhere('name', 'Industrial-Refinery')
                 ->orWhere('name', 'Industrial-Extraction')
                 ->orWhere('name', 'Tourism-High-Tech')
                 ->orWhere('name', 'High-Tech-Industrial')
                 ->orWhere('name', 'Refinery-High-Tech')
                 ->orWhere('name', 'High-Tech-Industrial-Extraction');
    }
    
    public function tradeRatio(State $state) {
        $supply = 0;
        $demand = 0;
        
        $export = Commodity::whereHas('reserves', function ($q) use ($state) {
            $q->where('reserves', '>', 0)
              ->where('current', 1)
              ->whereHas('station', function($q2) {
                  $q2->where('economy_id', $this->id);
              });
        })->with('commoditystat')->with('effects')->get();
        foreach ($export as $com) {
            $effect = $com->effects->where('state_id', $state->id)->first();
            if ($effect) {
                if ($com->supplycycle) {
                    $supply += $effect->supplysize * $com->commoditystat->supplymed / ($com->supplycycle/86400);
                }
            }
        }

        $import = Commodity::whereHas('reserves', function ($q) use ($state) {
            $q->where('reserves', '<', 0)
              ->where('current', 1)
              ->whereHas('station', function($q2) {
                  $q2->where('economy_id', $this->id);
              });
        })->with('commoditystat')->with('effects')->get();
        foreach ($import as $com) {
            $effect = $com->effects->where('state_id', $state->id)->first();
            if ($effect) {
                if ($com->demandcycle) {
                    $demand += $effect->demandsize * $com->commoditystat->demandmed / ($com->demandcycle/86400);
                }
            }
        }

        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }

    public function tradePriceRatio(State $state) {
       $supply = 0;
        $demand = 0;
        
        $export = Commodity::whereHas('reserves', function ($q) use ($state) {
            $q->where('reserves', '>', 0)
                ->where('current', 1)
                ->whereHas('station', function($q2) {
                    $q2->where('economy_id', $this->id);
                });
        })->with('commoditystat')->with('effects')->get();
        foreach ($export as $com) {
            $effect = $com->effects->where('state_id', $state->id)->first();
            if ($effect) {
                if ($com->supplycycle) {
                    $supply += $effect->supplysize * $effect->supplyprice * $com->averageprice * $com->commoditystat->supplymed / ($com->supplycycle/86400);
                }
            }
        }

        $import = Commodity::whereHas('reserves', function ($q) use ($state) {
            $q->where('reserves', '<', 0)
                ->where('current', 1)
                ->whereHas('station', function($q2) {
                    $q2->where('economy_id', $this->id);
                });
        })->with('commoditystat')->with('effects')->get();
        foreach ($import as $com) {
            $effect = $com->effects->where('state_id', $state->id)->first();
            if ($effect) {
                if ($com->demandcycle) {
                    $demand += $effect->demandsize * $effect->demandprice * $com->averageprice * $com->commoditystat->demandmed / ($com->demandcycle/86400);
                }
            }
        }

        if ($demand != 0) {
            return -$supply/$demand;
        }
        return null;
    }
}
