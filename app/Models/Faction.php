<?php

namespace App\Models;

use App\Models\System;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Faction extends Model
{
    //
    public function government() {
        return $this->belongsTo('App\Models\Government');
    }

    public function ethos() {
        return $this->belongsTo('App\Models\Ethos');
    }
    
    public function system() {
        return $this->belongsTo('App\Models\System');
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

    public function scopeNotHidden($q) {
        return $q->where('hidden', 0);
    }
    public function scopeNotVirtual($q) {
        return $q->where('virtual', 0);
    }
    
    public function latestSystems() {
        return $this->influences()->where('current', 1)
                    ->with('system', 'state', 'system.economy')
                    ->orderBy('influence', 'desc')->get();
    }

    // optimise for distances page
    public function systemCount() {
        return $this->influences()->where('current', 1)->count();
    }

    
    public function currentState(System $system) {
        $influence = $this->influences()->where('current', 1)
                          ->where('system_id', $system->id)
                          ->with('state')->first();
        if ($influence === null) {
            return null;
        } else {
            return $influence->state;
        }
    }

    // optimise for progress page so no need to look up state itself
    // as we're only interested in "lockdown or not"
    public function currentStateID(System $system) {
        $influence = $this->influences()->where('current', 1)
                          ->where('system_id', $system->id)
                          ->first();
        if ($influence === null) {
            return null;
        } else {
            return $influence->state_id;
        }
    }
    
    public function currentStates() {
        $influences = $this->influences()->where('current', 1)
                           ->with('state')->get();
        $states = [];
        $hold = null;
        foreach ($influences as $influence) {
            if ($influence->state->name != "None") {
                if (!isset($states[$influence->state->id])) {
                    $states[$influence->state->id] = $influence->state;
                }
            } else {
                $hold = $influence->state;
            }
        }
        if (count($states) > 0) {
            return $states;
        } else if ($hold) {
            return [$hold]; // return "None" if it's the only one
        } else {
            return []; // fallback in case of bad data
        }
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

    public function controlsAsset(System $system) {
        foreach ($system->stations as $station) {
            if ($station->faction_id == $this->id) {
                return true;
            }
        }
        return false;
    }

    public function currentRankString(System $system) {
        $influences = $system->latestFactions();
        
        foreach ($influences as $idx => $influence) {
            if ($influence->faction_id == $this->id) {
                return ($idx+1)." / ".count($influences);
            }
        }
        return "?";
    }

    public function currentInfluence(System $system) {
        $influences = $system->latestFactions();
        
        foreach ($influences as $idx => $influence) {
            if ($influence->faction_id == $this->id) {
                return $influence;
            }
        }
        return null;
    }

    /* Used for calculating previous-retreat avoidance in expansions */
    public function previouslyIn(System $system) {
        $in = Influence::where('system_id', $system->id)
            ->where('faction_id', $this->id)
            ->where('current', false)
            ->whereDate('date', '>=', new Carbon('2017-10-01'))
            ->count();
        return ($in > 0);
    }

    public function colour() {
        $h = hexdec(substr(md5($this->name), 0, 2)) * 360/256;
        $s = ((hexdec(substr(md5($this->name), 2, 2)) / 1.5)+64) * 100/256;
        $l = ((hexdec(substr(md5($this->name), 4, 2)) / 3)+64) * 100/256;

        /* HSL -> RGB conversion */
        $h /= 60;
        if ($h < 0) $h = 6 - fmod(-$h, 6);
        $h = fmod($h, 6);

        $s = max(0, min(1, $s / 100));
        $l = max(0, min(1, $l / 100));

        $c = (1 - abs((2 * $l) - 1)) * $s;
        $x = $c * (1 - abs(fmod($h, 2) - 1));

        if ($h < 1) {
            $r = $c;
            $g = $x;
            $b = 0;
        } elseif ($h < 2) {
            $r = $x;
            $g = $c;
            $b = 0;
        } elseif ($h < 3) {
            $r = 0;
            $g = $c;
            $b = $x;
        } elseif ($h < 4) {
            $r = 0;
            $g = $x;
            $b = $c;
        } elseif ($h < 5) {
            $r = $x;
            $g = 0;
            $b = $c;
        } else {
            $r = $c;
            $g = 0;
            $b = $x;
        }

        $m = $l - $c / 2;
        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);
        /* End conversion */
        
        $col = str_pad(dechex($r), 2, "0", STR_PAD_LEFT).
            str_pad(dechex($g), 2, "0", STR_PAD_LEFT).
            str_pad(dechex($b), 2, "0", STR_PAD_LEFT);

        return $col;
    }
}
