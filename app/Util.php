<?php

namespace App;

use Carbon\Carbon;

class Util {

    public static function selectMap($items, $empty=false, $alt=null) {
        $map = [];
        if ($empty) {
            $map[0] = "None";
        }
        foreach ($items as $item) {
            if ($alt) {
                $map[$item->id] = $item->$alt();
            } else {
                $map[$item->id] = $item->name;
            }
        }
        return $map;
    }

    public static function tick() {
        if (date("H") < env("TICK_TIME",15)) {
            $target = Carbon::yesterday();
        } else {
            $target = Carbon::now();
        }
        return $target;
    }

    public static function age($date) {
        return (new Carbon($date))->diffInDays(Carbon::now());
    }
    
    public static function displayDate($date) {
        return $date->format("j F ").(1286+$date->format("Y"));
    }
    public static function graphDisplayDate($date) {
        return $date->diffInDays(new Carbon("2017-03-01"));
    }
}