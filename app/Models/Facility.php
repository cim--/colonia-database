<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    public function stations() {
        return $this->belongsToMany('App\Models\Station')->withPivot('enabled');
    }

    public function systems() {
        return $this->belongsToMany('App\Models\System')->withPivot('enabled');
    }

    public static function stationFacilities() {
        return self::where('type', 'Station')->orderBy('name')->get();
    }

    public static function systemFacilities() {
        return self::where('type', 'System')->orderBy('name')->get();
    }
//
}
