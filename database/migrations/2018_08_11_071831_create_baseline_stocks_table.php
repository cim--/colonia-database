<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBaselineStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baselinestocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('station_id')->unsigned()->index();
            $table->integer('commodity_id')->unsigned()->index();
            $table->integer('reserves');
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
        Schema::dropIfExists('baselinestocks');
    }
}
