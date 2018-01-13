<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Module;

class AddLargeshipFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('largeship')->default(false);
        });

        // size 7 optional modules are always large
        Module::whereHas('moduletype', function ($q) {
            $q->where('type', 'optional');
        })->where('size', '>=', 7)->update(['largeship' => true]);
        // large ship armours are large        
        Module::whereHas('moduletype', function ($q) {
            $q->where('type', 'armour');
        })->whereIn('type', [
            "Imperial Clipper", "Orca", "Type-9 Heavy", "Beluga Liner", "Anaconda", "Federal Corvette", "Imperial Cutter", "Type-10 Defender", "Type-7 Transporter"
        ])->update(['largeship' => true]);
        // core components are variable
        Module::whereHas('moduletype', function ($q) {
            $q->where('description', 'Power Plant')
              ->orWhere('description', 'Power Distributor');
        })->where('size', '>=', 8)->update(['largeship' => true]);
        Module::whereHas('moduletype', function ($q) {
            $q->where('description', 'Thrusters')
              ->orWhere('description', 'Sensors')
              ->orWhere('description', 'Fuel Tank');
        })->where('size', '>=', 7)->update(['largeship' => true]);
        Module::whereHas('moduletype', function ($q) {
            $q->where('description', 'Frame Shift Drive')
              ->orWhere('description', 'Life Support');
        })->where('size', '>=', 6)->update(['largeship' => true]);
        // utilities, hardpoints, optionalns are always small
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('largeship');
        });
    }
}
