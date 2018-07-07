<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Megaship extends Model
{
    protected $dates = ["created_at", "updated_at", "commissioned", "decommissioned"];
    
    public function megashipclass()
    {
        return $this->belongsTo('App\Models\Megashipclass');
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
