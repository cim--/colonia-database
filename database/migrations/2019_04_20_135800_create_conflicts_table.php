<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConflictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conflicts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_id')->unsigned()->index();
            $table->string('type');
            $table->string('status');
            $table->integer('faction1_id')->unsigned()->index();
            $table->integer('faction2_id')->unsigned()->index();
            $table->integer('asset1_id')->nullable()->unsigned();
            $table->string('asset1_type')->nullable();
            $table->integer('asset2_id')->nullable()->unsigned();
            $table->string('asset2_type')->nullable();
            $table->string('score');
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
        Schema::dropIfExists('conflicts');
    }
}
