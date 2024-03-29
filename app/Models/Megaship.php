<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Megaship extends Model
{
    protected $casts = ["created_at" => 'datetime', "updated_at" => 'datetime', "commissioned" => 'datetime', "decommissioned" => 'datetime'];

    protected $firstmove = "2018-07-05";
   
    public function megashipclass()
    {
        return $this->belongsTo('App\Models\Megashipclass');
    }

    public function megashiprole()
    {
        return $this->belongsTo('App\Models\Megashiprole');
    }

    
    public function megashiproutes()
    {
        return $this->hasMany('App\Models\Megashiproute');
    }

    public function sequenceCount()
    {
        return $this->megashiproutes->count();
    }
    
    public function currentSequence()
    {
        if ($this->megashiproutes->count() == 0) {
            return null;
        }
        if ($this->megashiproutes->count() == 1) {
            return $this->megashiproutes->first();
        }
        if ($this->megashipclass->operational) {
            $max = $this->megashiproutes->max('sequence');
            $weeks = $this->commissioned->diffInWeeks();
            $phase = $this->commissioned->diffInWeeks(Carbon::parse($this->firstmove));

            if ($this->commissioned->lt('2020-07-10')) {
                /* 11, 18, 25 June and 2, 9 July 2020 were skipped without affecting phase */
                $weeks -= 4;
            }
            if ($this->commissioned->lt('2021-05-20')) {
                /* 20 May 2021 appeared to update the counter twice */
                $weeks += 1;
            }

            if ($phase % 2 == 1) {
                $moves = floor(($weeks+1)/2);
            } else {
                $moves = floor($weeks/2);
            }
            
            $sequence = $moves % ($max+1);
            return $this->megashiproutes->where('sequence', $sequence)->first();        } else {
            return $this->megashiproutes->first();
        }
    }
    
    public function currentLocation()
    {
        $sequence = $this->currentSequence();
        if ($sequence == null) {
            return "Unknown";
        }
        if ($sequence->system_id) {
            return $sequence->system;
        } else {
            return $sequence->systemdesc;
        }
    }

    public function currentLocationName()
    {
        $sequence = $this->currentSequence();
        if ($sequence == null) {
            return "Unknown";
        }
        if ($sequence->system_id) {
            return $sequence->system->displayName();
        } else {
            return $sequence->systemdesc;
        }
    }
    
    public function displayName()
    {
        return $this->megashipclass->name." ".$this->serial;
    }

    public function firstMove()
    {
        return Carbon::parse($this->firstmove);
    }
}
