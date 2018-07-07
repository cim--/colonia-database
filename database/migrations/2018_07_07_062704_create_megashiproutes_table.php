<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMegashiproutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('megashiproutes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('megaship_id')->index();
            $table->integer('sequence')->unsigned();
            $table->integer('system_id')->nullable()->index();
            $table->integer('system_desc')->nullable();
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
        Schema::dropIfExists('megashiproutes');
    }
}
