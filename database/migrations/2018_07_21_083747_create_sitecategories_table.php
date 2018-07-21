<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Sitecategory;

class CreateSitecategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sitecategories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $cats = ["Tip-off", "Listening Post", "Tourist Beacon", "Facility", "Attacked Ship", "Alien"];
        foreach ($cats as $cat) {
            $sitecat = new Sitecategory;
            $sitecat->name = $cat;
            $sitecat->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sitecategories');
    }
}
