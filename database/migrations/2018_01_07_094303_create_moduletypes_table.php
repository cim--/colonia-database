<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModuletypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moduletypes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->enum('type', ['core', 'optional', 'optionalns', 'utility', 'hardpoint', 'armour', 'personalweapon', 'suit']);
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
        Schema::dropIfExists('moduletypes');
    }
}
