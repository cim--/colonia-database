<?php

namespace App;

use App\Models\Conflict;
use App\Models\Commodity;
use App\Models\System;
use App\Models\Influence;
use App\Models\State;
use App\Models\History;
use App\Models\Engineer;
use App\Models\Module;

use Carbon\Carbon;

class ArticleManager {

    public $template = 'radio.templates.pending';
    public $parameters = [];
    public $picker = null;

    public function load($sequence) {
        $offset = date("z")*443;
        $article = $sequence + $offset;
        $seed = $article*229;

        $this->picker = new ArticlePicker($seed);

        $cycle = 11;
        
        $type = $article % $cycle;

        //        $type = 7; $entry = $article;
        switch ($type) {
            // intro
        case 0: return $this->loadHeadline();
            // news headlines
        case 1: return $this->loadConflicts();
        case 2: return $this->loadEvents();
        case 3: return $this->loadMovements();
        case 4: return $this->loadMarket();
            // advert
        case 5: return $this->loadAdvert();
            // articles
        case 6: return $this->loadHelp();
        case 7: return $this->loadTraffic();
            // advert
        case 8: return $this->loadAdvert();
            // articles
        case 9: return $this->loadSpotlight();
        case 10: return $this->loadMisc();
        }
    }

    /* Special bulletins, if available, general welcome if not */
    private function loadHeadline() {
        // TODO: actual special bulletins
        $this->template = 'radio.templates.intro';
        $this->parameters = [];
    }

    /* Information on current conflicts */
    private function loadConflicts() {
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
    private function loadMarket() {
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
    private function loadEvents() {
        $systems = System::populated()->orderBy('id')->get();
        $types = ['Outbreak', 'Pirate Attack', 'Blight', 'Drought', 'Terrorism', 'Infrastructure Failure', 'Natural Disaster', 'Public Holiday'];
        $consequences = ['Lockdown', 'Civil Unrest', 'Bust', 'Famine'];
        
        $affected = $systems->filter(function($v) use ($types) {
            $f = $v->controllingFaction();
            $states = $f->currentStateList($v);
            if (!$states) {
                return false;
            }
            foreach ($states as $state) {
                if (in_array($state->name, $types)) {
                    return true;
                }
            }
            return false;
        })->values();

        $system = $this->picker->pickFrom($affected);
        $faction = $system->controllingFaction();
        $states = $faction->currentStateList($system);
        $eventstate = $states->filter(function($v) use ($types) {
            if (in_array($v->name, $types)) {
                return true;
            }
            return false;
        })->first();
        $constates = $states->filter(function($v) use ($consequences) {
            if (in_array($v->name, $consequences)) {
                return true;
            }
            return false;
        }); 

        $this->template = 'radio.templates.events.'.strtolower(str_replace(" ", "", $eventstate->name));
        $this->parameters = [
            'system' => $system,
            'faction' => $faction,
            'event' => $eventstate,
            'outcomes' => $constates
        ];
    }

    /* System spotlight articles */
    private function loadSpotlight() {
        $systems = System::populated()->whereNotNull('name')->orderBy('id')->get();
        $system = $this->picker->pickFrom($systems);

        $this->template = 'radio.templates.spotlight.intro';
        $this->parameters = [
            'population' => $system->population,
            'name' => $system->name,
            'station' => $system->mainStation()->name,
            'faction' => $system->controllingFaction()->name,
            'detail' => 'radio.templates.spotlight.systems.'.strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $system->name))
        ];
        
    }

    /* Expansions and retreats */
    private function loadMovements() {
        $expansion = State::where('name', 'Expansion')->first();
        $retreat = State::where('name', 'Retreat')->first();
        
        $expansions = Influence::where('current', 1)->whereHas('states', function($q) use ($expansion) {
            $q->where('states.id', $expansion->id);
        })->get();
        if ($expansions->count() > 0) {
            $expanding = $this->picker->pickFrom($expansions);
        } else {
            $expanding = null; // unlikely but possible
        }
        
        $retreats = Influence::where('current', 1)->whereHas('states', function($q) use ($retreat) {
            $q->where('states.id', $retreat->id);
        })->get();
        
        if ($retreats->count() > 0) {
            $retreating = $this->picker->pickFrom($retreats);
        } else {
            $retreating = null; // unlikely but possible
        }

        $histories = History::movement()->recent()->get();
        if ($histories->count() > 0) {
            $history = $this->picker->pickFrom($histories);
        } else {
            $history = null; // sometimes
        }

        
        $this->template = 'radio.templates.movement.report';
        $this->parameters = [
            'expanding' => $expanding,
            'retreating' => $retreating,
            'history' => $history
        ];
    }

    /* Help articles */
    private function loadHelp() {
        $this->template = 'radio.templates.help.intro';
        $article = $this->picker->pickFrom([
            'location',
            'trading',
            'exploration',
            'combat',
            'piracy',
            'missions',
            'mining',
            'outfitting',
            'engineering',
            'tourism'
        ]);
        
        $this->parameters = [
            'article' => 'intro.new.'.$article,
            'systemcount' => System::where('population', '>', 0)->count(),
            'totalPopulation' => System::sum('population')
        ];
    }

    /* Sponsorship pieces */
    private function loadAdvert() {
        $this->template = "radio.templates.adverts.intro";
    }

    /* Traffic reports */
    private function loadTraffic() {
        $this->template = "radio.templates.traffic.report";
        $systems = System::populated()->orderBy('id')->get();
        $system = $this->picker->pickFrom($systems);

        $report = $system->systemreports()->orderBy('date', 'desc')->first();
        $average = $system->systemreports()->where('estimated', 0)->where('date', '>', Carbon::parse("-1 year"))->avg('traffic');
        $haslarge = $system->stations()->largeDockable()->count();
        $this->parameters = [
            'system' => $system,
            'report' => $report->traffic,
            'law' => ($report->crimes + $report->bounties)/2,
            'average' => $average,
            'haslarge' => $haslarge
        ];
    }
    
    /* Misc broadcasts - lower frequency content with its own subdivisions */
    private function loadMisc() {
        switch ($this->picker->pick(2)) {
        case 0: return $this->loadEngineers();
        case 1: return $this->loadOutfitting();
        }
    }

    private function loadEngineers() {
        $engineers = Engineer::whereHas('blueprints', function($q) {
            $q->where('level', '<', 5);
        })->orderBy('id')->get();

        $engineer = $this->picker->pickFrom($engineers);

        $blueprints = $engineer->blueprints()->where('level', '<', 5)->orderBy('id')->get();
        $blueprint = $this->picker->pickFrom($blueprints);

        $finished = $engineer->blueprints()->where('level', '=', 5)->orderBy('id')->get();
        $finish = $this->picker->pickFrom($finished);

        $this->template = "radio.templates.misc.engineer";
        $this->parameters = [
            'engineer' => $engineer,
            'blueprint' => $blueprint,
            'system' => $engineer->station->system,
            'station' => $engineer->station,
            'finished' => $finish->moduletype->description
        ];
    }

    private function loadOutfitting() {
        $modules = Module::isAvailable()->orderBy('id')->get();
        $module = $this->picker->pickFrom($modules);

        $this->template = "radio.templates.misc.module";

        $stations = $module->stations;
        if ($stations->count() <= 5) {
            $dstations = $stations;
        } else {
            $dstations = $stations->slice($this->picker->pick($stations->count()-5), 5);
        }
        
        $this->parameters = [
            'module' => $module,
            'stations' => $stations,
            'dstations' => $dstations
        ];
    }
}
