<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $table = 'historys';
    
    protected $dates = [
        'created_at',
        'updated_at',
        'date'
    ];

    public function location() {
        return $this->morphTo();
    }

    public function faction() {
        return $this->belongsTo('App\Models\Faction');
    }
}
