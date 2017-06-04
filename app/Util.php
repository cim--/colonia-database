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

    public static function nearTick() {
        // was the expected tick less than an hour ago?
        return (date("H") == env("TICK_TIME",15));
    }
    
    public static function age($date) {
        return (new Carbon($date))->diffInDays(Carbon::now());
    }
    
    public static function displayDate($date) {
        if (!$date) {
            return "";
        }
        return $date->format("j F ").(1286+$date->format("Y"));
    }
    public static function graphDisplayDate($date) {
        return $date->diffInDays(new Carbon("2017-03-01"));
    }

    public static function stateColour($state) {
        $colours = [
            "None" => "#000000",
            "Boom" => "#006060",
            "Bust" => "#000060",
            "Civil Unrest" => "#005000",
            "Lockdown" => "#405000",
            "War" => "#703000",
            "Election" => "#503030",
            "Expansion" => "#002050",
            "Investment" => "#404040",
            "Retreat" => "#900000",
            "Famine" => "#600060",
            "Outbreak" => "#200050"
        ];
        if (isset($colours[$state])) {
            return $colours[$state];
        }
        return "#000000";
    }

    public static function magnitude($mag) {
        switch ($mag) {
        case "-3": return "---";
        case "-2": return "--";
        case "-1": return "-";
        case "3": return "+++";
        case "2": return "++";
        case "1": return "+";
        }
        return "";
    }
    public static function sign($sign) {
        if ($sign < 0) {
            return "&#x2B07;";
        } else if ($sign > 0) {
            return "&#x2B06;";
        }
        return "";
    }

    public static function coloniaCoordinates($traditional) {
        // translate
        $x = $traditional->x + 9530.5;
        $cy = $traditional->y + 910.28125;
        $z = $traditional->z - 19808.125;
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

    public static function distance($a, $b) {
        return sqrt(
            ($a->x - $b->x) * ($a->x - $b->x) +
            ($a->y - $b->y) * ($a->y - $b->y) +
            ($a->z - $b->z) * ($a->z - $b->z)
        );
    }
}