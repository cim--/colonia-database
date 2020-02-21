<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

use App\ArticleManager;

class ArticleController extends Controller
{
    public function index() {
        $sequence = (int)date("H")*60 + (int)date("i");
        return $this->show($sequence);
    }

    public function show($sequence) {
        $am = new ArticleManager;
        $am->load($sequence);
        
        return view('radio.index', [
            'template' => $am->template,
            'parameters' => $am->parameters,
            // 'picker' => $am->picker,
            'sequence' => $sequence
        ]);
    }
    
    // api call
    public function article($article) {
        $am = new ArticleManager;
        $am->load($article);
        
        return view($am->template, [
            'parameters' => $am->parameters,
            // 'picker' => $am->picker,
            'sequence' => $article
        ]);
    }
}
