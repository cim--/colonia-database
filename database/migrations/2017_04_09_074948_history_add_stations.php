<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HistoryAddStations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historys', function (Blueprint $table) {
            $table->renameColumn('system_id', 'location_id');
            $table->string('location_type')->default('App\Models\System')->after('location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historys', function (Blueprint $table) {
            $table->renameColumn('location_id', 'system_id');
            $table->dropColumn('location_type');
        });
    }
}
