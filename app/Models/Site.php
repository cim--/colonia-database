<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    public function sitecategory() {
        return $this->belongsTo('App\Models\Sitecategory');
    }

    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public function displayName() {
        return $this->system->displayName()." ".$this->planet." ".$this->summary;
    }

}
