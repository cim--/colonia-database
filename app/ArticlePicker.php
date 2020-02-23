<?php

namespace App;

use Carbon\Carbon;

class ArticlePicker {

    private $state = 0;
    
    public function __construct($initial) {
        $this->state = $initial % 1024;
    }

    private function step() {
        // basic LCG cycler
        $this->state = (($this->state + 317) * 463) % 1024;
    }

    public function pick($size) {
        $this->step();
        return (int)floor($this->state * $size / 1024);
    }

    public function pickFrom($list) {
        $option = $this->pick(count($list));
        return $list[$option];
    }
}
