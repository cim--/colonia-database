<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEddneventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eddnevents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_id');
            $table->datetime('eventtime');
            $table->timestamps();
            $table->index('system_id');
            $table->index('eventtime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eddnevents');
    }
}
