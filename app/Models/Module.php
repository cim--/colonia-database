<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Station;

class Module extends Model
{
    public function moduletype()
    {
        return $this->belongsTo('App\Models\Moduletype');
    }

    public function stations()
    {
        return $this->belongsToMany('App\Models\Station')->withPivot('current', 'unreliable')->withTimestamps();
    }

    /* A module is available if either:
     * - it doesn't require a large ship and is available at a station
     * - it is available at a station with large pads
     */
    public function scopeIsAvailable($q, $current=false) {
        return $q->where(function($smq) use ($current) {
            $smq->whereHas('stations', function ($ssq) use ($current) {
                $ssq->present();
                if ($current) {
                    $ssq->where('current', true);
                }
            })
                ->where('largeship', 0);
        })->orWhere(function($lq) use ($current) {
            $lq->whereHas('stations', function($ssq) use ($current) {
                $ssq->present();
                $ssq->whereHas('stationclass', function ($scq) {
                    $scq->where('hasLarge', 1);
                });
                if ($current) {
                    $ssq->where('current', true);
                }
            });
        });
    }

    public function scopeIsAvailableAtStation($q, Station $station, $current=false) {
        if ($station->stationclass->hasLarge) {
            return $q->whereHas('stations', function ($sq) use ($station, $current) {
                $sq->where('stations.id', $station->id);
                if ($current) {
                    $sq->where('current', true);
                }
            });
        } else {
            return $q->whereHas('stations', function ($sq) use ($station, $current) {
                $sq->where('stations.id', $station->id);
                if ($current) {
                    $sq->where('current', true);
                }
            })->where('largeship', 0);
        }
    }
    
    public function displayName()
    {
        if ($this->moduletype->type == "hardpoint") {
            $name = $this->moduletype->description." ";
            switch ($this->size) {
            case 1: $name .= "Small"; break;
            case 2: $name .= "Medium"; break;
            case 3: $name .= "Large"; break;
            case 4: $name .= "Huge"; break;
            }
            $name .= " ".$this->type;
            return $name;
        } else if ($this->moduletype->type == "optionalns") {
            return $this->moduletype->description;
        } else if ($this->moduletype->type == "utility") {
            return $this->moduletype->description." ".$this->type;
        } else {
            return $this->moduletype->description." ".$this->size.$this->type;
        }
    }
}
