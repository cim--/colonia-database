<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionFactoryFrequencyField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('economy_region', function (Blueprint $table) {
            $table->decimal('factoryfrequency', 8, 1)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('economy_region', function (Blueprint $table) {
            $table->dropColumn('factoryfrequency');
        });
    }
}
