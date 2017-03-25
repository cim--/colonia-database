<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Systemreport extends Model
{
    protected $fillable = ['date', 'system_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'date'
    ];
    
    public function system() {
        return $this->belongsTo('App\Models\System');
    }
}
