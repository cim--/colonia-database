<?php

namespace App;

use Carbon\Carbon;

class ArticlePicker {

    private $state = 0;
    
    public function __construct($initial) {
        $this->state = $initial % 8192;
        // spin
        $this->step();
        $this->step();
        $this->step();
    }

    private function step() {
        // basic LCG cycler
        $this->state = (($this->state * 2621) + 317) % 8192;
    }

    public function pick($size) {
        $this->step();
        return (int)floor($this->state * $size / 8192);
    }

    public function pickFrom($list) {
        $option = $this->pick(count($list));
        return $list[$option];
    }
}
