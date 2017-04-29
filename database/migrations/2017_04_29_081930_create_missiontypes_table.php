<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissiontypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missiontypes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->tinyInteger('reputationMagnitude');
            $table->tinyInteger('sourceInfluenceMagnitude');
            $table->integer('sourceState_id');
            $table->tinyInteger('sourceStateMagnitude');
            $table->boolean('hasDestination');
            $table->tinyInteger('destinationInfluenceMagnitude')->nullable();
            $table->integer('destinationState_id')->nullable();
            $table->tinyInteger('destinationStateMagnitude')->nullable();
            
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
        Schema::dropIfExists('missiontypes');
    }
}
