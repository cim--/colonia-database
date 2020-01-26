<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EffectsAddPassField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('effects', function (Blueprint $table) {
            $table->tinyInteger('spass')->unsigned()->default(1);
            $table->tinyInteger('dpass')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('effects', function (Blueprint $table) {
            $table->dropColumn('dpass');
            $table->dropColumn('spass');
        });
    }
}
