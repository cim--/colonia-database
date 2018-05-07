<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReliabilityIndicators extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('module_station', function (Blueprint $table) {
            $table->boolean('current')->default(true);
            $table->boolean('unreliable')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('module_station', function (Blueprint $table) {
            $table->dropColumn('current');
            $table->dropColumn('unreliable');
        });
    }
}
