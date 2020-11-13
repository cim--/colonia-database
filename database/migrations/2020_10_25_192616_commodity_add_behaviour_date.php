<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Commodity;

class CommodityAddBehaviourDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->date('behaviourepoch')->nullable()->default("2020-01-23");
        });
        Commodity::whereIn('name', [
            "LowTemperatureDiamond",
            "rhodplumsite",
            "serendibite",
            "monazite",
            "musgravite",
            "benitoite",
            "grandidierite",
            "alexandrite",
            "painite",
            "opal",
            "agronomictreatment"
        ])->update(['behaviourepoch' => '2020-06-09']);
        Commodity::whereIn('name', [
            "tritium",
        ])->update(['behaviourepoch' => '2020-06-12']);
        Commodity::whereIn('name', [
            "Bauxite",
            "Gallite",
            "Rutile",
            "PowerGenerators",
            "ThermalCoolingUnits",
            "BuildingFabricators",
        ])->update(['behaviourepoch' => '2020-10-02']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->dropColumn('behaviourepoch');
        });
    }
}
