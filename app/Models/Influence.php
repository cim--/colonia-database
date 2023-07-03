<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Influence extends Model
{
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date' => 'datetime'
    ];
    
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function faction() {
        return $this->belongsTo('App\Models\Faction');
    }

    public function states() {
        return $this->belongsToMany('App\Models\State');
    }


    public function displayDate() {
        return \App\Util::displayDate($this->date); 
    }

    public function happinessString() {
        return self::happinessAsString($this->happiness);
    }

    public static function happinessAsString($happiness) {
        switch ($happiness) {
        case "1": return "Elated";
        case "2": return "Happy";
        case "3": return "Discontented";
        case "4": return "Unhappy";
        case "5": return "Despondent";
        }
        return "";
    }

}
