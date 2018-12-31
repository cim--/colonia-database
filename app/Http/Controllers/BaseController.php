<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Systemreport;
use App\Models\Faction;
use App\Models\Station;
use App\Models\History;
use App\Models\Influence;
use App\Models\Alert;
use App\Models\State;
use App\Models\Region;
use App\Models\Reserve;
use App\Models\Economy;
use App\Models\Government;
use App\Models\Facility;
use App\Models\Megashipclass;
use App\Models\Installationclass;
use App\Models\Installation;
use App\Models\Megaship;
use App\Models\Engineer;
use App\Models\Project;

class BaseController extends Controller
{
    public function index() {

        $wordmap = [];
        
        $history = History::with('location', 'location.economy', 'faction', 'faction.government')
            ->where('date', '>=', Carbon::yesterday()->format("Y-m-d"))
            ->orderBy('date', 'desc')->get();

        $influences = Influence::with('system', 'system.stations', 'system.economy', 'faction', 'faction.government', 'states')
            ->where('current', 1)
            ->get()->sort(function($a, $b) {
                $cmp = strcmp($a->system->displayName(), $b->system->displayName());
                if ($cmp != 0) {
                    return $cmp;
                }
                return $b->influence - $a->influence;
            });

        $important = $influences->filter(function ($value, $key) {
            if (!$value->system || !$value->system->inhabited()) {
                return false; // safety for bad data
            }
            $use = false;
            foreach ($value->states as $state) {
                $states = ['Boom', 'Investment', 'Civil Liberty', 'None'];
                // ignore uninteresting and positive states
                if (in_array($state->name, $states)) {
                    continue;
                }
                $states = ['War', 'Election', 'Expansion'];
                if (!in_array($state->name, $states)) {
                    if ($value->system->controllingFaction()->id != $value->faction->id) {
                        continue; // ignore most states for non-controlling factions
                    }
                }
                if ($state->name == "Expansion" && $value->system_id != $value->faction->system_id) {
                    continue; // hide expansions for all but home system
                    // Later: maybe we'll get expansion source in the journal
                }

                $use = true;
                break;
            }
            return $use;
        });
        
        $lowinfluences = [];
        $sysid = 0;
        $happiness = 0;
        $happinesses = [1=>0,2=>0,3=>0,4=>0,5=>0];
        
        foreach ($influences as $influence) {

            $happiness += (25*(5-$influence->happiness)) * $influence->influence * $influence->system->population / 100;
            $happinesses[$influence->happiness] += $influence->influence * $influence->system->population / 100;
            
            if ($influence->system_id != $sysid) {
                if ($influence->system->controllingFaction()->id != $influence->faction_id) {
                    $lowinfluences[] = $influence->system;
                } 
                    
                $sysid = $influence->system_id;
            }
            // don't need to consider the others
        }
        
        
        $systems = System::with('phase', 'economy', 'facilities')->orderBy('name')->get();
        $factions = Faction::with('government', 'ethos')->notHidden()->orderBy('name')->get();
        $stations = Station::with('economy', 'stationclass')->orderBy('name')->get();

        
        $statescalc = [];
        $states = [];
        $statecounter = 0;
        $none = null;
        foreach ($factions as $faction) {
            $statescalc[$faction->id] = [];
        }
        foreach ($influences as $influence) {
            foreach ($influence->states as $state) {
                if ($state->name != "None") {
                    if (!isset($statescalc[$influence->faction_id][$state->id])) {
                        $statescalc[$influence->faction_id][$state->id] = $state;
                    }
                } else {
                    $none = $state;
                }
            }
        }
        foreach ($statescalc as $faction) {
            if (count($faction) == 0) {
                $faction = [$none];
            }
            foreach ($faction as $state) {
                if (!isset($states[$state->name])) {
                    $states[$state->name] = ['state' => $state, 'count' => 0, 'syscount' => 0];
                }
                $states[$state->name]['count']++;
            }
        }
        foreach ($factions as $faction) {
            if ($faction->virtual) {
                continue;
            }
            $controls = $faction->stations->where('primary', true);
            foreach ($controls as $station) {
                $fstates = $faction->currentStateList($station->system);
                if ($fstates != null) {
                    foreach ($fstates as $state) {
                        if (!isset($states[$state->name])) {
                            $states[$state->name] = ['state' => $state, 'count' => 0, 'syscount' => 0];
                        }
                        $states[$state->name]['syscount']++;
                    }
                }
            }
        }
        
        ksort($states);
        
        $population = System::sum('population');
        $exploration = System::sum('explorationvalue');

        $iconmap = [];
        $economies = [];
        foreach ($stations as $station) {
            if ($station->stationclass->hasSmall) {
                if (!isset($economies[$station->economy->name])) {
                    $economies[$station->economy->name] = 0;
                }
                $economies[$station->economy->name]++;
                $iconmap[$station->economy->name] = $station->economy->icon;
            }
            
            $this->wordmap($wordmap, $station->name);
        }

        $governments = [];
        $ethoses = [];
        foreach ($factions as $faction) {
            if (!isset($governments[$faction->government->name])) {
                $governments[$faction->government->name] = 0;
            }
            $governments[$faction->government->name]++;

            if (!$faction->virtual) {
                if (!isset($ethoses[$faction->ethos->name])) {
                    $ethoses[$faction->ethos->name] = 0;
                }
                $ethoses[$faction->ethos->name]++;
            }

            $iconmap[$faction->government->name] = $faction->government->icon;

            $this->wordmap($wordmap, $faction->name);
        }
        arsort($governments);

        $avgcoordinates = \App\Util::coloniaCoordinates((object)[
            'x' => System::where('population', '>', 0)->avg('x'),
            'y' => System::where('population', '>', 0)->avg('y'),
            'z' => System::where('population', '>', 0)->avg('z')
        ]);
        $maxdist = 0;
        $ecsize = 0;
        $terraformable = 0;
        $water = 0;
        $earthlike = 0;
        $ammonia = 0;
        foreach ($systems as $system) {
            if ($system->population > 0) {
                $ecsize += $system->economySize();
                $ccoords = $system->coloniaCoordinates();
                $dist = \App\Util::distance($ccoords, $avgcoordinates);
                if ($dist > $maxdist) {
                    $maxdist = $dist;
                }
                $terraformable += $system->cfthmc;
                $water += $system->cftww;
                if ($system->facilities->where('name', 'Earthlike')->count() > 0) {
                    $earthlike++;
                }
                if ($system->facilities->where('name', 'Ammonia World')->count() > 0) {
                    $ammonia++;
                }
                $this->wordmap($wordmap, $system->displayName());
            }

        }
        $coldist = \App\Util::distance($avgcoordinates, (object)['x'=>0,'y'=>0,'z'=>0]);

        $bounties = Systemreport::where('current', 1)->sum('bounties')/1000000;
        $maxtraffic = Systemreport::where('current', 1)->max('traffic');
        $mintraffic = Systemreport::where('current', 1)->min('traffic');

        $ethoslabels = ["Ethos"];
        $ethosdatasets = [];
        foreach ($ethoses as $type => $count) {
            $ethosdatasets[] = [
                'data' => [$count],
                'label' => $type,
                'backgroundColor' => \App\Util::ethosColour($type)
            ];
        }

        $ethoschart = app()->chartjs
            ->name("ethoses")
            ->type("horizontalBar")
            ->size(["height" => 100, "width"=>500])
            ->labels($ethoslabels)
            ->datasets($ethosdatasets)
            ->options([
                'scales' => [
                    'yAxes' => [
                        [ 'stacked' => true ]
                    ],
                    'xAxes' => [
                        [
                            'stacked' => true,
                            'ticks' => [
                                'min' => 0,
                                'max' => $factions->count()
                            ],

                        ],
                    ],
                ],
            ]);

        foreach ($wordmap as $key => $count) {
            if ($wordmap[$key] == 1) {
                //unset($wordmap[$key]);
            }
        }

        $projects = Project::where('complete', false)->orderBy('priority')->orderBy('summary')->get();
        
        return view('index', [
            'population' => $population,
            'happiness' => $happiness,
            'happinesses' => $happinesses,
            'exploration' => $exploration,
            'terraformable' => $terraformable,
            'elwcount' => $earthlike,
            'wwcount' => $water,
            'awcount' => $ammonia,
            'populated' => $systems->filter(function($v) { return $v->population > 0; })->count(),
            'unpopulated' => $systems->filter(function($v) { return $v->population == 0; })->count(),
            'dockables' => $stations->filter(function($v) { return $v->stationclass->hasSmall; })->count(),
            'players' => $factions->filter(function($v) { return $v->player; })->count(),
            'economies' => $economies,
            'governments' => $governments,
            'ethoschart' => $ethoschart,
            'states' => $states,
            'systems' => $systems,
            'factions' => $factions,
            'stations' => $stations,
            'engineers' => Engineer::count(),
            'projects' => $projects,
            'historys' => $history,
            'importants' => $important,
            'lowinfluences' => $lowinfluences,
            'fakeglobals' => ['Retreat', 'Expansion'],
            'iconmap' => $iconmap,
            'maxdist' => $maxdist,
            'coldist' => $coldist,
            'bounties' => $bounties,
            'maxtraffic' => $maxtraffic,
            'mintraffic' => $mintraffic,
            'wordmap' => $wordmap,
            'ecsize' => $ecsize,
            'megacount' => Megaship::where('decommissioned', null)->count(),
            'instcount' => Installation::count()
        ]);
    }
//

    private function wordmap(&$map, $string) {
        $blacklist = ["the", "and"];

        $string = str_replace("Ra' Takakhan", html_entity_decode("Ra&nbsp;'Takakhan"), $string);
        $words = explode(" ", $string);
        foreach ($words as $word) {
            $word = strtolower(trim($word));
            if (strlen($word) > 2 && !in_array($word, $blacklist)) {
                if (!isset($map[$word])) {
                    $map[$word] = 0;
                }
                $map[$word]++;
            }
        }
    }
    
    public function progress() {
        $user = \Auth::user();
        /*          // now allows anonymous read-only access
        if (!$user) {
            \App::abort(403);
        }

        if ($user->rank == 0) {
            return view('progressno');
            } */

        $today = Carbon::now();
        $target = \App\Util::tick();
        $influenceupdate = System::where('population', '>', 0)
            ->where('virtualonly', 0)
            ->whereDoesntHave('influences', function($q) use ($target) {
                $q->where('date', $target->format("Y-m-d 00:00:00"));
            })->orderBy('catalogue')->get();

        $reportsupdate = System::where('population', '>', 0)
            ->where('virtualonly', 0)
            ->whereDoesntHave('systemreports', function($q) use ($today) {
                $q->where('date', $today->format("Y-m-d 00:00:00"))
                  ->where('estimated', false);
            })->orderBy('catalogue')->get();

        $marketsupdate = Station::whereDoesntHave('reserves', function($q) use ($today) {
            $q->where('date', $today->format("Y-m-d 00:00:00"));
        })->whereHas('stationclass', function($q) {
            $q->where('hasSmall', true)
              ->orWhere('hasMedium', true)
              ->orWhere('hasLarge', true);
        })->whereHas('facilities', function($q) {
            $q->where('name', 'Commodities');
        })->with('faction', 'system')->orderBy('name')->get();


        $reader = strpos(`pgrep -af cdb:ed[d]nreader`, 'cdb:eddnreader');

        $alerts = Alert::where('processed', false)->orderBy('created_at')->get();
        $lockdown = State::where('name', 'Lockdown')->first();
        
        return view('progress', [
            'target' => $target,
            'today' => $today,
            'userrank' => $user ? $user->rank : 0, // TODO: Composer
            'influenceupdate' => $influenceupdate->sort('\App\Util::systemSort'),
            'reportsupdate' => $reportsupdate->sort('\App\Util::systemSort'),
            'marketsupdate' => $marketsupdate,
            'influencecomplete' => 100*(1-($influenceupdate->count() / System::populated()->where('virtualonly', 0)->count())),
            'reportscomplete' => 100*(1-($reportsupdate->count() / System::populated()->where('virtualonly', 0)->count())),
            'marketscomplete' => 100*(1-($marketsupdate->count() / Station::dockable()->count())),
            'reader' => $reader,
            'alerts' => $alerts,
            'lockdown' => $lockdown
        ]);
    }

    public function acknowledgeAlert(Alert $alert) {
        $user = \Auth::user();
        if (!$user) {
            \App::abort(403);
        }
        if ($user->rank < 2) {
            \App::abort(403);
        } 
        
        $alert->processed = true;
        $alert->save();
        return redirect()->route('progress')->with('status',
        [
            'success' => 'Alert acknowledged'
        ]);
    }
    
    public function about() {
        return view('intro/about');
    }

    public function story() {
        return view('intro/story');
    }

    
    public function newToColonia() {
        return view('intro/new', [
            'systemcount' => System::where('population', '>', 0)->count(),
            'totalPopulation' => System::sum('population')
        ]);
    }


    public function regions() {
        return view('intro/regions', [
            'systemcount' => System::where('population', '>', 0)->count(),
            'stationcount' => Station::count(),
            'factioncount' => Faction::notHidden()->count(),
            'totalPopulation' => System::sum('population'),
            'commodityReserves' => Reserve::where('current', 1)->where('reserves', '>', 0)->sum('reserves'),
            'commodityDemand' => -Reserve::where('current', 1)->where('reserves', '<', 0)->sum('reserves'),
            'economies' => Economy::where('compare', '1')->with(['regions', 'systems', 'stations' => function($q) {
                    $q->whereHas('stationclass', function($sc) {
                        $sc->where('hasSmall', 1);
                    });
                }
            ])->orderBy('name')->get(),
            'governments' => Government::orderBy('name')->with(['regions', 'factions' => function($q) {
                    $q->notHidden();
                }])->get(),
            'regions' => Region::orderBy(\DB::raw('name = "Deep Space"'))->orderBy('population', 'desc')->with('economies', 'governments')->get()
        ]);

    }


    public function icons() {
        return view('intro/icons', [
            'economies' => Economy::orderBy('name')->get(),
            'governments' => Government::orderBy('name')->get(),
            'states' => State::orderBy('name')->get(),
            'facilities' => Facility::orderBy('name')->get(),
            'megaships' => Megashipclass::orderBy('name')->get(),
            'installations' => Installationclass::orderBy('name')->get(),
        ]);
    }
}
