<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eddnblacklist extends Model
{
    public static function blacklist($hash) {
        if (!Eddnblacklist::where('blacklisted', $hash)->first()) {
            $blacklist = new Eddnblacklist;
            $blacklist->blacklisted = $hash;
            $blacklist->save();

            \Log::info('Blacklisted EDDN feed ID', [
                'id' => $hash
            ]);
        }
    }

    public static function check($hash) {
        return (Eddnblacklist::where('blacklisted', $hash)->count() > 0);
    }
}
