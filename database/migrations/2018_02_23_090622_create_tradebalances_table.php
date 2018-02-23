<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradebalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tradebalances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('economy_id')->index();
            $table->integer('state_id')->index();
            $table->float('volumebalance')->nullable();
            $table->float('creditbalance')->nullable();
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
        Schema::dropIfExists('tradebalances');
    }
}
