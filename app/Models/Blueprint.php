<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blueprint extends Model
{
    protected $fillable = ['engineer_id', 'moduletype_id'];
    
    public function engineer()
    {
        return $this->belongsTo('App\Models\Engineer');
    }

    public function moduletype()
    {
        return $this->belongsTo('App\Models\Moduletype');
    }

    public function scopeUnduplicated($q)
    {
        return $q->whereNotIn('moduletype_id', [2,3,4,5,44,51,52,56]);
    }
}
