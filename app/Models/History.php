<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

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

    public function scopeMovement($q) {
        return $q->whereIn('description', [
            "expanded to", "expanded by invasion to", "retreated from",
        ]);
    }

    public function scopeOwnership($q) {
        return $q->whereIn('description', [
            "lost control of", "took control of"
        ]);
    }

    public function scopeRecent($q) {
        return $q->whereDate('date', '>=', Carbon::parse("-2 days"));
    }
}
