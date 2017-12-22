<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->string('name', 191)->change();
            $table->index('name');
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->string('name', 191)->change();
            $table->index('name');
            $table->index('system_id');
            $table->index('faction_id');
        });
        Schema::table('influences', function (Blueprint $table) {
            $table->index(['system_id', 'faction_id']);
        });
        Schema::table('expansioncaches', function (Blueprint $table) {
            $table->index('system_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
        Schema::table('stations', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['system_id']);
            $table->dropIndex(['faction_id']);
        });
        Schema::table('influences', function (Blueprint $table) {
            $table->dropIndex(['system_id', 'faction_id']);
        });
        Schema::table('expansioncaches', function (Blueprint $table) {
            $table->dropIndex(['system_id']);
        });
    }
}
