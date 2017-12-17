<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public static function alert($contents) {
        $alert = new Alert;
        $alert->alert = $contents;
        $alert->save();
    }
    //
}
