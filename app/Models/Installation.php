<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    protected $dates = ['created_at', 'updated_at', 'constructed'];

    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function faction() {
        return $this->belongsTo('App\Models\Faction');
    }

    public function installationclass() {
        return $this->belongsTo('App\Models\Installationclass');
    }

    public function displayName() {
        if ($this->name) {
            return $this->name;
        }
        return $this->system->displayName()." ".$this->planet." ".$this->installationclass->name;
    }

    public function displayType() {
        return "installation";
    }
    
    public function displayRoute() {
        return 'installations.show';
    }

    public function isController() {
        return false;
    }
}
