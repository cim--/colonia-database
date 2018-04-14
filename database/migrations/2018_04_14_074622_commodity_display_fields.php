<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Commodity;

class CommodityDisplayFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $entries = file("database/migrations/commodity.csv");

        Schema::table('commodities', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->string('description')->nullable();
        });

        foreach ($entries as $entry) {
            trim($entry);
            list($id, $code, $category, $desc) = explode(",", $entry);
            $commodity = Commodity::where('name', $code)->first();
            if ($commodity) {
                $commodity->category = $category;
                $commodity->description = $desc;
                $commodity->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('commodities', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('description');
        });
    }
}
