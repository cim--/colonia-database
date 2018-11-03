<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReserveStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reserve_state', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reserve_id')->index();
            $table->integer('state_id')->index();
            $table->timestamps();
        });

        \DB::insert("INSERT INTO reserve_state (reserve_id, state_id) (SELECT id, state_id FROM reserves)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reserve_state');
    }
}
