<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEngineersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('engineers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('discovery');
            $table->text('invitation');
            $table->text('access');
            $table->integer('faction_id');
            $table->integer('station_id');
            $table->timestamps();
        });

        $gov = new App\Models\Government;
        $gov->name = 'Engineer';
        $gov->icon = 'icons/governments/engineer';
        $gov->save();

        $stat = new App\Models\Stationclass;
        $stat->name = 'Engineer Base';
        $stat->orbital = false;
        $stat->hasSmall = true;
        $stat->hasMedium = true;
        $stat->hasLarge = true;
        $stat->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('engineers');
    }
}
