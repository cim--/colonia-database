<?php

namespace App;

use Carbon\Carbon;

class Util {

    public static function selectMap($items) {
        $map = [];
        foreach ($items as $item) {
            $map[$item->id] = $item->name;
        }
        return $map;
    }

    public static function tick() {
        if (date("H") < 15) {
            $target = Carbon::yesterday();
        } else {
            $target = Carbon::now();
        }
        return $target;
    }
    
}