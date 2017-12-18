<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public static function alert($contents) {
        if (Alert::where('alert', $contents)->where('processed', false)->count() > 0) {
            return; // already alerted
        }
        $alert = new Alert;
        $alert->alert = $contents;
        $alert->save();
    }
    //
}
