<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commoditystat extends Model
{
    protected $fillable = ['commodity_id'];

    public function commodity() {
        return $this->belongsTo('App\Models\Commodity');
    }

    public function getLevel($intensity) {
        if ($intensity < 0) {
            return $this->getDemandLevel(-$intensity);
        } else if ($intensity > 0) {
            return $this->getSupplyLevel($intensity);
        } else {
            return 0;
        }
    }

    protected function getDemandLevel($intensity) {
        if ($this->demandmin === null) {
            return 0;
        }
        if ($intensity < ($this->demandmin+$this->demandlowq)/2) {
            return 1;
        } else if ($intensity < ($this->demandlowq+$this->demandmed)/2) {
            return 2;
        } else if ($intensity <= ($this->demandmed+$this->demandhighq)/2) {
            return 3;
        } else if ($intensity <= ($this->demandhighq+$this->demandmax)/2) {
            return 4;
        } else {
            return 5;
        }
    }

    protected function getSupplyLevel($intensity) {
        if ($this->supplymin === null) {
            return 0;
        }
        if ($intensity < ($this->supplymin+$this->supplylowq)/2) {
            return 1;
        } else if ($intensity < ($this->supplylowq+$this->supplymed)/2) {
            return 2;
        } else if ($intensity <= ($this->supplymed+$this->supplyhighq)/2) {
            return 3;
        } else if ($intensity <= ($this->supplyhighq+$this->supplymax)/2) {
            return 4;
        } else {
            return 5;
        }
    }

}
