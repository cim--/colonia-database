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

    public function scopeMajor($q) {
        return $q->whereNotIn('description', [
            "expanded to", "expanded by invasion to", "retreated from",
            "lost control of", "took control of"
        ]);
    }
}
