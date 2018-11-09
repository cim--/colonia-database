<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Influence extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'date'
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
        switch ($this->happiness) {
        case "1": return "Elated";
        case "2": return "Happy";
        case "3": return "Discontented";
        case "4": return "Unhappy";
        case "5": return "Despondent";
        }
        return "";
    }
}
