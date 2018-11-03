<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfluenceStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influence_state', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('influence_id')->index();
            $table->integer('state_id')->index();
            $table->timestamps();
        });

        \DB::insert("INSERT INTO influence_state (influence_id, state_id) (SELECT id, state_id FROM influences)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('influence_state');
    }
}
