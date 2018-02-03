<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Economy;
use App\Models\Government;

class AddVirtualFactionFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('factions', function (Blueprint $table) {
            $table->boolean('virtual')->default(false);
        });

        $economy = new Economy;
        $economy->name = "Prison";
        $economy->icon = "icons/economies/prison";
        $economy->analyse = false;
        $economy->save();

        $government = new Government;
        $government->name = "Detention Centre";
        $government->icon = "icons/governments/detention";
        $government->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('factions', function (Blueprint $table) {
            $table->dropColumn('boolean');
        });
    }
}
