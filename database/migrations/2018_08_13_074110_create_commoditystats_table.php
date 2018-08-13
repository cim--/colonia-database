<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommoditystatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('commoditystats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('commodity_id')->index();
            $table->float('demandmin')->nullable();
            $table->float('demandlowq')->nullable();
            $table->float('demandmed')->nullable();
            $table->float('demandhighq')->nullable();
            $table->float('demandmax')->nullable();
            $table->float('supplymin')->nullable();
            $table->float('supplylowq')->nullable();
            $table->float('supplymed')->nullable();
            $table->float('supplyhighq')->nullable();
            $table->float('supplymax')->nullable();
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
        Schema::dropIfExists('commoditystats');
    }
}
