<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeStationclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stationclasses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->boolean('orbital');
            $table->boolean('hasSmall');
            $table->boolean('hasMedium');
            $table->boolean('hasLarge');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stationclasses');
    }
}
