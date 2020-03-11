<?php

namespace App;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\Influence;
use App\Models\State;

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

    public static function fairlyNearTick() {
        // was the expected tick less than four hours ago?
        $tick = env("TICK_TIME",15);
        if ($tick <= 20) {
            return (
                date("H") >= $tick &&
                date("H") < $tick + 4
            );
        } else {
            return (
                date("H") >= $tick ||
                date("H") < $tick - 20
            );
        }
    }

    
    public static function age($date, $target) {
        //dd($date, $target, $date->diffInHours($target));
        return ceil($date->diffInHours($target)/24);
    }
    
    public static function displayDate($date) {
        if (!$date) {
            return "";
        }
        return $date->format("j F ").(1286+$date->format("Y"));
    }
    public static function formDisplayDate($date) {
        if (!$date) {
            return "";
        }
        return (1286+$date->format("Y")).$date->format("-m-d");
    }
    public static function graphDisplayDate($date) {
        return $date->diffInDays(new Carbon("2017-03-01"));
    }
    public static function graphDisplayDateTime($date) {
        return $date->diffInSeconds(new Carbon("2017-12-22"));
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
            "Outbreak" => "#200050",
            "Pirate Attack" => "#666666",
            "Civil Liberty" => "#004070",
            "Drought" => "#802000",
        ];
        if (isset($colours[$state])) {
            return $colours[$state];
        }
        return "#000000";
    }

    public static function ethosColour($state) {
        $colours = [
            "Social" => "#309030",
            "Corporate" => "#306090",
            "Authoritarian" => "#603090",
            "Criminal" => "#903030",
            "Unknown" => "#505050"
        ];
        if (isset($colours[$state])) {
            return $colours[$state];
        }
        return "#000000";
    }

    public static function wordColour($word, $count) {
        if ($count == 1) {
            return str_replace(
                [0,2,4,6,8,"a"],
                ["a","b","c","d","e","f"],
                substr(md5($word), 0, 6)
            );
        } else {
            return str_replace(
                ["a","b","c","d","e","f"],
                [0,2,4,6,8,"a"],
                substr(md5($word), 0, 6)
            );
        }
    }
    
    public static function magnitude($mag) {
        switch ($mag) {
        case "-5": return "-----";
        case "-4": return "----";
        case "-3": return "---";
        case "-2": return "--";
        case "-1": return "-";
        case "5": return "+++++";
        case "4": return "++++";
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
        $cx = $traditional->x - 359.84375;
        $cy = $traditional->y + 385.53125;
        $cz = $traditional->z + 718.37500;
        // rotate
/*        $theta = -1.0033;
        $cx = ($x*cos($theta))+($z*sin($theta));
        $cz = (-$x*sin($theta))+($z*cos($theta)); */
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

    public static function systemSort($a, $b) {
        return strcmp($a->displayName(), $b->displayName());
    }


    public static function stateBars(Faction $faction, $stateos, $percent = false) {
        $states = [];
        foreach ($stateos as $state) {
            $states[$state->id] = $state;
        }
        $total = 0;
        
        $statedata = [];
        $infs = \DB::select("SELECT influence_state.state_id, COUNT(*) AS ct FROM influence_state INNER JOIN influences ON (influence_id = influences.id) WHERE faction_id = ? GROUP BY influence_state.state_id", [$faction->id]);

        foreach ($infs as $inf) {
            $statedata[$states[$inf->state_id]->name] = $inf->ct;
            $total += $inf->ct;
        }
        
        $datasets = [];
        foreach ($statedata as $state => $counter) {
            $datasets[$state] = [
                'label' => $state,
                'data' => [$percent ? round($counter*100/$total, 2) : $counter],
                'backgroundColor' => \App\Util::stateColour($state)
            ];
        }
        return $datasets;
    }

    public static function sigFig($number, $figures=3) {
        if ($number == 0) {
            return 0;
        }
        $l = log(abs($number), 10);
        $dig = floor($l)+1;
        $factor = $dig-$figures;
        //        dd($l, $dig, $figures, $number, $factor);
        if ($factor <= 0) {
            return round($number, $figures-$dig);
        } else {
            return (10**$factor)*round($number/(10**$factor));
        }
    }
}
