<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\System;
use App\Models\History;

class UpdateHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:history {--tick=0} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh the history table';

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
        \DB::transaction(function() {
            $debug = $this->option('debug');
            
            if ($tick = $this->option('tick')) {
                $tick = new Carbon($tick);
            } else {
                $tick = \App\Util::tick();
            }
            $previous = $tick->copy()->subDay();
            
            History::where('date', $tick->format("Y-m-d 00:00:00"))->delete();

            $systems = System::where('population', '>', 0)->get();
            foreach ($systems as $system) {
                $getfactions = $system->factions($tick);
                $getlastfactions = $system->factions($previous);
                if (count($getfactions) == 0 || count($getlastfactions) == 0) {
                    continue; // no data
                }

                $factions = $this->map($getfactions);
                $lastfactions = $this->map($getlastfactions);
                

                foreach ($factions as $faction) {
                    if (!$lastfactions->contains($faction)) {
                        if ($debug) {
                            $this->line($system->displayName().": expansion - ".$faction->name);
                        }
                        $history = new History;
                        $history->location_id = $system->id;
                        $history->location_type = 'App\Models\System';
                        $history->faction_id = $faction->id;
                        $history->date = $tick;
                        $history->expansion = true;
                        $history->save();
                    }
                }
                
                foreach ($lastfactions as $faction) {
                    if (!$factions->contains($faction)) {
                        if ($debug) {
                            $this->line($system->displayName().": retreat - ".$faction->name);
                        }
                        $history = new History;
                        $history->location_id = $system->id;
                        $history->location_type = 'App\Models\System';
                        $history->faction_id = $faction->id;
                        $history->date = $tick;
                        $history->expansion = false;
                        $history->save();
                    }
                }

                
            }

        });
        //
    }

    private function map($infs) {
        $factions = [];
        foreach ($infs as $inf) {
            $factions[] = $inf->faction;
        }
        return collect($factions);
    }
}
