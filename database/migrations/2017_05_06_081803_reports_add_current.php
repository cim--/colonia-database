<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReportsAddCurrent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('systemreports', function (Blueprint $table) {
            $table->boolean('current')->default(false);
            $table->index('current');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('systemreports', function (Blueprint $table) {
            $table->dropColumn('current');
        });
    }
}
