<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('systemreports', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_id')->index();
            $table->date('date')->index();
            $table->integer('crime');
            $table->integer('bounties');
            $table->integer('traffic');
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
        Schema::dropIfExists('systemreports');
    }
}
