<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Economy;

class AddHybridEconomies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('economies', function (Blueprint $table) {
            $table->boolean('analyse')->default(true);
        });

        $e1 = new Economy;
        $e1->analyse = false;
        $e1->name = "Industrial-Refinery";
        $e1->icon = "icons/economies/industrialrefinery";
        $e1->save();

        $e2 = new Economy;
        $e2->analyse = false;
        $e2->name = "Industrial-Extraction";
        $e2->icon = "icons/economies/industrialextraction";
        $e2->save();

        $e3 = new Economy;
        $e3->analyse = false;
        $e3->name = "Thargoid Interference";
        $e3->icon = "icons/economies/thargoidinterference";
        $e3->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('economies', function (Blueprint $table) {
            $table->dropColumn('analyse');
        });
    }
}
