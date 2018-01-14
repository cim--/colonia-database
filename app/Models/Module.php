<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public function moduletype()
    {
        return $this->belongsTo('App\Models\Moduletype');
    }

    public function stations()
    {
        return $this->belongsToMany('App\Models\Station');
    }

    /* A module is available if either:
     * - it doesn't require a large ship and is available at a station
     * - it is available at a station with large pads
     */
    public function scopeIsAvailable($q) {
        return $q->where(function($smq) {
            $smq->whereHas('stations')
                ->where('largeship', 0);
        })->orWhere(function($lq) {
            $lq->whereHas('stations', function($ssq) {
                $ssq->whereHas('stationclass', function ($scq) {
                    $scq->where('hasLarge', 1);
                });
            });
        });
    }
    
    public function displayName()
    {
        return $this->moduletype->description." ".$this->size.$this->type;
    }
}