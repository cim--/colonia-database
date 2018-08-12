<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCycleestimateField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->enum('cycleestimate', ['Neither', 'Demand', 'Supply', 'Both'])->default('Neither');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->dropColumn('cycleestimate');
        });
    }
}
