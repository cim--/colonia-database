<?php

namespace App;

use Carbon\Carbon;

class ArticlePicker {

    private $state = [0,0,0];
    
    public function __construct($initial) {
        $this->state[0] = ($initial % 29999)+1;
        $this->state[1] = (($initial*2621) % 29999)+1;
        $this->state[2] = (($initial*317) % 29999)+1;
    }

    private function step() {
        //Wichmann-Hill Algorithm
        $this->state[0] = ($this->state[0]*171) % 30269;
        $this->state[1] = ($this->state[1]*172) % 30307;
        $this->state[2] = ($this->state[2]*170) % 30323;
    }

    public function pick($size) {
        $this->step();

        $value = ($this->state[0]/30269)+($this->state[1]/30307)+($this->state[2]/30323);
        $fraction = $value - floor($value);

        return (int)floor($fraction * $size);
    }

    public function pickFrom($list) {
        $option = $this->pick(count($list));
        return $list[$option];
    }
}
