<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{

    
    public function phase() {
        return $this->belongsTo('App\Models\Phase');
    }

    public function economy() {
        return $this->belongsTo('App\Models\Economy');
    }

    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function stations() {
        return $this->hasMany('App\Models\Station');
    }

    public function systemreports() {
        return $this->hasMany('App\Models\Systemreport');
    }
//
    public function inhabited() {
        return $this->population > 0;
    }
    
    public function displayName() {
        if ($this->name) {
            return $this->name;
        } else {
            return $this->catalogue;
        }
    }

    public function coloniaCoordinates() {
        // translate
        $x = $this->x + 9530.5;
        $cy = $this->y + 910.28125;
        $z = $this->z - 19808.125;
        // rotate
        $theta = -1.0033;
        $cx = ($x*cos($theta))+($z*sin($theta));
        $cz = (-$x*sin($theta))+($z*cos($theta));
        $coords = new \StdClass;
        $coords->x = $cx;
        $coords->y = $cy;
        $coords->z = $cz;
        return $coords;
    }
    
    public function distanceTo(System $other) {
        $distance = sqrt(
            (($this->x - $other->x)*($this->x - $other->x)) +
            (($this->y - $other->y)*($this->y - $other->y)) +
            (($this->z - $other->z)*($this->z - $other->z))
        );
        return number_format($distance, 2);
    }

    public function refreshEDSM() {
        $name = $this->displayName();
        // temp for names in EDSM
        if ($name == "Canis Subridens") { $name = "Tas"; }
        if ($name == "Aurora Astrum") { $name = $this->catalogue; }
        
        $url = "https://www.edsm.net/api-v1/system?systemName=".
            urlencode($name).
            "&showCoordinates=1&showId=1";
        $json = file_get_contents($url);
        $data = json_decode($json);
        if (isset($data->coords)) {
            $this->x = $data->coords->x;
            $this->y = $data->coords->y;
            $this->z = $data->coords->z;
            $this->edsm = $data->id;
        } else {
            $this->x = $this->y = $this->z = $this->edsm = 0;
        }
    }

    public function controllingFaction()
    {
        $station = $this->stations->where('primary', true)->first();
        if ($station) {
            return $station->faction;
        }
        return null;
    }
    
    public function latestFactions() {
        $date = new Carbon($this->influences()->max('date'));
        return $this->factions($date);
    }

    public function factions(Carbon $date) {
        return $this->influences()->whereDate('date', $date->format("Y-m-d"))
            ->with('faction', 'state')->orderBy('influence', 'desc')->get();
    }

    public function latestReport() {
        $date = new Carbon($this->systemreports()->max('date'));
        return $this->report($date);
    }

    public function report(Carbon $date) {
        return $this->systemreports()
                    ->whereDate('date', $date->format("Y-m-d"))
                    ->first();
    }

}
