<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Megaship extends Model
{
    protected $dates = ["created_at", "updated_at", "commissioned", "decommissioned"];

    /* Megaships usually move weekly, but sometimes 'stall'. This
     * array is used to track weeks they stay still. */
    protected $slips = ["2018-07-12", "2018-07-26", "2018-08-09"];
    
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
        if ($this->megashipclass->operational) {
            $max = $this->megashiproutes->max('sequence');
            $weeks = $this->commissioned->diffInWeeks();
            foreach ($this->slips as $idx => $slip) {
                if ($this->commissioned->lt(Carbon::parse($slip))) {
                    $weeks -= count($this->slips)-$idx;
                    break;
                }
            }
            
            $sequence = $weeks % ($max+1);
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

    public function displayName()
    {
        return $this->megashipclass->name." ".$this->serial;
    }
}
