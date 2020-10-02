<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
use App\Models\Faction;
use App\Models\System;
use App\Models\State;
use App\Models\Influence;

class EventGaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:eventgaps';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyse gaps between events';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {

            $states = State::whereIn('id', [12,14,15,16,17,18,19,20])->get();
            $lookup = [8 => [8 => 99]];
            $statemap = [8 => "Expansion"];
            foreach ($states as $state) {
                $statemap[$state->id] = $state->name;
                $lookup[$state->id] = [8 => 99];
                foreach ($states as $state2) {
                    $lookup[$state->id][$state2->id] = 99;
                    $lookup[8][$state2->id] = 99;
                }
            }

            $infs = Influence::where('date', '>=', '2020-01-15')
                  //                  ->where('system_id', 1)
                  ->with('states')->orderBy('system_id')->orderBy('faction_id')->orderBy('date')->cursor();
            $lastsys = 0;
            $lastfact = 0;
            $lastevent = -1;
            $lastdate = Carbon::parse("2020-01-15");
            $gap = 0;
            foreach ($infs as $inf) {
                
                if (($inf->faction_id != $lastfact || $inf->system_id != $lastsys)) {
                    // $this->line("---");
                    $lastevent = -1;
                    $gap = 0;
                    $lastsys = $inf->system_id;
                    $lastfact = $inf->faction_id;
                    $lastdate == $inf->date->copy();
                }

                $event = $this->event($inf);

                //$this->line($event." (".$inf->system_id.",".$inf->faction_id.",".$inf->date->format("Ymd").")");
                
                if ($event != 0) {
                    if ($lastevent == -1) {
                        $lastevent = $event;
                        $gap = 0;
                    } else if ($gap > 0) {
                        $this->recordGap($lookup, $lastevent, $event, $gap, $statemap, $inf);
                        $lastevent = $event;
                        $gap = 0;
                    }
                } else {
                    if ($lastevent != -1) {
                        if ($lastdate->diffInDays($inf->date) > 3) {
                            // treat as retreat+expand
                            $gap = 0;
                            $lastevent = 8;
                        } else {
                            $gap += $lastdate->diffInDays($inf->date);
                        }
                    }
                }
                
                $lastdate = $inf->date->copy();
            }

            $line = "Fv T> ";
            foreach ($statemap as $id => $row) {
                if ($id != 8) {
                    $line .= " ".substr($row,0,3)." ";
                }
            }
            $line .= " ANY";
            $this->info($line);
            foreach ($statemap as $id => $row) {
                $line = substr($row,0,5)." ";
                $min = 99;
                foreach ($statemap as $id2 => $row2) {
                    if ($id2 != 8) {
                        if ($lookup[$id][$id2] == 99) {
                            $line .= " --  ";
                        } else {
                            if ($min > $lookup[$id][$id2]) {
                                $min = $lookup[$id][$id2];
                            }
                            $line .= " ".$lookup[$id][$id2]."  ";
                        }
                    }
                }
                $line .= " ".$min;
                $this->line($line);
            }
            $line = "ANY   ";
            $minmin = 99;
            foreach ($statemap as $id => $row) {
                if ($id != 8) {
                    $min = 99;
                    foreach ($statemap as $id2 => $row2) {
                        if ($min > $lookup[$id2][$id]) {
                            $min = $lookup[$id2][$id];
                            if ($minmin > $min) {
                                $minmin = $min;
                            }
                        }
                    }
                    $line .= " ".$min."  ";
                }
            }
            $line .= " ".$minmin;
            $this->line($line);
            
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->line($e->getTraceAsString());
        }
    }

    private function event($inf) {
        foreach ($inf->states as $s) {
            if (in_array($s->id, [12,14,15,16,17,18,19,20])) {
                return $s->id;
            }
        }
        return 0;
    }

    private function recordGap(&$lookup, $last, $now, $gap, $statemap, $inf)
    {
        if ($lookup[$last][$now] > $gap) {
            $lookup[$last][$now] = $gap;
            $msg = $statemap[$last]."->".$statemap[$now]." = ".$gap." (".$inf->system_id.",".$inf->faction_id.",".$inf->date->format("Ymd").")";
            if ($gap < 14) {
                $this->error($msg);
            } else {
                $this->line($msg);
            }
        }
    }
}
