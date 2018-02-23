<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tradebalance extends Model
{
    protected $fillable = ['economy_id', 'state_id'];
    
    public function economy()
    {
        return $this->belongsTo('App\Models\Economy');
    }

    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }

}
