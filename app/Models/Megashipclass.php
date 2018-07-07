<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Megashipclass extends Model
{
    public function megaship()
    {
        return $this->hasMany('App\Models\Megaship');
    }
}
