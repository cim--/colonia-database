<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('catalogue');
            $table->string('name')->nullable();
            $table->decimal('x', 10, 5);
            $table->decimal('y', 10, 5);
            $table->decimal('z', 10, 5);
            $table->string('edsm')->nullable();
            $table->integer('economy_id')->nullable();
            $table->integer('population');
            $table->integer('phase_id');
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
        Schema::dropIfExists('systems');
    }
}
