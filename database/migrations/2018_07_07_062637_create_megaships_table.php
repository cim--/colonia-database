<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMegashipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('megaships', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('megashipclass_id');
            $table->string('serial');
            $table->date('commissioned')->nullable();
            $table->date('decommissioned')->nullable();
            $table->text('cargodesc')->nullable();
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
        Schema::dropIfExists('megaships');
    }
}
