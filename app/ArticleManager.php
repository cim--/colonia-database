<?php

namespace App;

use Carbon\Carbon;

class ArticleManager {

    public $template = 'radio.templates.intro';
    public $parameters = [];
    public $picker = null;

    function load($sequence) {
        // TODO
        $this->template = 'radio.templates.intro';
        $this->parameters = [];
        
    }

}
