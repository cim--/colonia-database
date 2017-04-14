<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Faction extends Model
{
    //
    public function government() {
        return $this->belongsTo('App\Models\Government');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }

    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function history() {
        return $this->hasMany('App\Models\History');
    }
    
    // pending states
    public function states() {
        return $this->belongsToMany('App\Models\State')->withPivot('date');
    }

    public function latestSystems() {
        return $this->influences()->where('current', 1)
                    ->with('system', 'state', 'system.economy')
                    ->orderBy('influence', 'desc')->get();
    }
    
    public function systems(Carbon $date) {
        return $this->influences()->whereDate('date', $date->format("Y-m-d"))
                    ->with('system', 'state', 'system.economy')
                    ->orderBy('influence', 'desc')->get();
    }

    public function abbreviation() {
        $words = explode(" ", $this->name);
        $abbrev = "";
        if (count($words) == 1) {
            return $this->name;
        }
        foreach ($words as $word) {
            if (in_array($word, ['and', 'the', 'de', 'of'])) {
                $abbrev .= substr($word, 0, 1);
            } else if (is_numeric($word)) {
                $abbrev .= $word;
            } else {
                $abbrev .= strtoupper(substr($word, 0, 1));
            }
        }
        return $abbrev;
    }
}
