<?php

namespace App;

class Util {

    public static function selectMap($items) {
        $map = [];
        foreach ($items as $item) {
            $map[$item->id] = $item->name;
        }
        return $map;
    }

    
}