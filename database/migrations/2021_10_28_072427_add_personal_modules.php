<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\Moduletype;
use App\Models\Module;

class AddPersonalModules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mtypes = ['suit' =>
                   [
                       'Jump Assist',
                       'Improved Life Support',
                       'Sprint Duration',
                       'Increased Ammunition Reserves',
                       'Armour Rating',
                       'Shield Regeneration',
                       'Increased Melee Damage',
                       'Backpack Capacity',
                       'Improved Radar',
                       'Efficient Tools',
                       'Battery Capacity',
                       'Quieter Footsteps',
                       'Night Vision'
                   ],
                   'personalweapon' =>
                   [
                       'Atmospheric Noise Suppression',
                       'Accuracy',
                       'Handling',
                       'ADS Movement',
                       'Clip Size',
                       'Reload Speed',
                       'Stowed Reloading',
                       'Increased Range',
                       'Scope',
                       'Stability',
                       'Synthetic Noise Suppression',
                       'Headshot Damage'
                   ]
        ];
        
        foreach ($mtypes as $mtype => $modules) {
            foreach ($modules as $module) {
                $mt = new Moduletype;
                $mt->description = $module;
                $mt->type = $mtype;
                $mt->save();

                $m = new Module;
                $m->moduletype_id = $mt->id;
                $m->eddn = '_personal_equipment_'.$module;
                $m->size = '0';
                $m->type = 'A';
                $m->largeship = 0;
                $m->restricted = 1;
                $m->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
