<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Ship;
use App\Models\Moduletype;
use App\Models\Module;

class AddNewShip extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cdb:addnewship {--ship=} {--desc=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \DB::transaction(function() {
        $ship = $this->option('ship');
        $desc = $this->option('desc');

        if (!$ship || !$desc) {
            $this->error("Ship and desc options required");
            exit(1);
        }

        $this->line("Adding ship");
        $s = new Ship;
        $s->eddn = $ship;
        $s->name = $desc;
        $s->save();

        $this->makeArmour("Lightweight Alloy", "Grade1", $ship, $desc);
        $this->makeArmour("Reinforced Alloy", "Grade2", $ship, $desc);
        $this->makeArmour("Military Grade Composite", "Grade3", $ship, $desc);
        $this->makeArmour("Mirrored Surface Composite", "Mirrored", $ship, $desc);
        $this->makeArmour("Reactive Surface Composite", "Reactive", $ship, $desc);
        $this->line("Done...");
        });
    }

    public function makeArmour($atype, $eddn, $ship, $desc) {
        $this->line("Adding armour: ".$atype);
        $t = Moduletype::where('description', $atype)->first();

        $m = new Module;
        $m->moduletype_id = $t->id;
        $m->eddn = $ship."_Armour_".$eddn;
        $m->size = null;
        $m->type = $desc;
        $m->largeship = 0;
        $m->restricted = 1;
        $m->save();
    }
}
