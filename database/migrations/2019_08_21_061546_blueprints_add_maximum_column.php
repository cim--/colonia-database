<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BlueprintsAddMaximumColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->integer('maxlevel')->default(5)->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blueprints', function (Blueprint $table) {
            $table->dropColumn('maximum');
        });
    }
}
