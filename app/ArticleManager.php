<?php

namespace App;

use App\Models\Conflict;
use App\Models\Commodity;

class ArticleManager {

    public $template = 'radio.templates.pending';
    public $parameters = [];
    public $picker = null;

    public function load($sequence) {
        $offset = date("z")*443;
        $article = $sequence+$offset;

        $this->picker = new ArticlePicker($article);
        
        $type = $article % 8;
        $entry = floor($article / 8);

        $type = 2; $entry = $article;
        switch ($type) {
        case 0: return $this->loadHeadline($entry);
        case 1: return $this->loadConflicts($entry);
        case 2: return $this->loadMarket($entry);
        case 3: return $this->loadEvents($entry);
        case 4: return $this->loadSpotlight($entry);
        case 5: return $this->loadMovements($entry);
        case 6: return $this->loadHelp($entry);
        case 7: return $this->loadMisc($entry);
        }
    }

    /* Special bulletins, if available, general welcome if not */
    private function loadHeadline($entry) {
        // TODO: actual special bulletins
        $this->template = 'radio.templates.intro';
        $this->parameters = [];
    }

    /* Information on current conflicts */
    private function loadConflicts($entry) {
        $conflicts = Conflict::orderBy('system_id')->get();
        $conflict = $this->picker->pickFrom($conflicts);

        $template = strtolower($conflict->type).strtolower($conflict->status);

        list($score1, $score2) = explode("-",$conflict->score);
        $direction = $score1 - $score2;
        $assets = [];
        if ($conflict->asset1) { $assets[] = $conflict->asset1; }
        if ($conflict->asset2) { $assets[] = $conflict->asset2; }
        
        $this->template = 'radio.templates.conflicts.'.$template; // TODO, variety
        $this->parameters = [
            'conflict' => $conflict,
            'direction' => $direction,
            'assets' => $assets
        ];
    }

    /* Information on market data */
    private function loadMarket($entry) {
        $commodities = Commodity::whereHas('reserves', function($q) {
            $q->where('reserves', '>', 0); // avoid mining-only, foreign imports, for now
            $q->where('current', 1);
        })->whereNotNull('description')->whereNotNull('category')->orderBy('id')->get();
        $commodity = $this->picker->pickFrom($commodities);

        $reserves = $commodity->reserves()->where('current', 1)->get();
        
        $minsell = 1e9;
        $maxsell = 0;
        $minbuy = 1e9;
        $maxbuy = 0;
        $supply = 0;
        $demand = 0;

        foreach ($reserves as $reserve) {
            if ($reserve->reserves > 0) {
                $supply += $reserve->reserves;
                if ($reserve->price < $minsell) { $minsell = $reserve->price; }
                if ($reserve->price > $maxsell) { $maxsell = $reserve->price; }
            } else {
                $demand -= $reserve->reserves;
                if ($reserve->price < $minbuy) { $minbuy = $reserve->price; }
                if ($reserve->price > $maxbuy) { $maxbuy = $reserve->price; }
            }
        }

        $this->template = 'radio.templates.markets.commodity'; // TODO: variety

        //dd($supply, $commodity->supplycycle/86400, $demand, $commodity->demandcycle/86400);
        
        $this->parameters = [
            'commodity' => $commodity,
            'minsell' => Util::sigFig($minsell),
            'maxsell' => Util::sigFig($maxsell),
            'minbuy' => Util::sigFig($minbuy),
            'maxbuy' => Util::sigFig($maxbuy),
            'supply' => Util::sigFig($supply),
            'demand' => Util::sigFig($demand),
            'surplus' => Util::sigFig(($supply * 86400 / $commodity->supplycycle) + ($demand * 86400 / $commodity->demandcycle))
        ];
        
    }

    /* Information on event states (pirate attack, etc) */
    private function loadEvents($entry) {

    }

    /* System spotlight articles */
    private function loadSpotlight($entry) {

    }

    /* Expansions and retreats */
    private function loadMovements($entry) {

    }

    /* Help articles */
    private function loadHelp($entry) {

    }

    /* Misc broadcasts - lower frequency content with its own subdivisions */
    private function loadMisc($entry) {

    }
}
