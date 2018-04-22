<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEconomyRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('economy_region', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('economy_id')->unsigned();
            $table->integer('region_id')->unsigned()->index();
            $table->integer('frequency');
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
        Schema::dropIfExists('economy_region');
    }
}
