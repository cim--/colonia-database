<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Megashipclass;

class CreateMegashipclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('megashipclasses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icon');
            $table->boolean('operational');
            $table->timestamps();
        });

        $classes = [
            "Gordon Class Bulk Cargo Ship" => "bulkcargo",
            "James Class Bulk Cargo Ship" => "bulkcargo",
            "Hogan Class Bulk Cargo Ship" => "bulkcargo",
            "Aquarius Class Tanker" => "tanker",
            "Freedom Class Survey Vessel" => "survey",
            "Bowman Class Science Vessel" => "science"
        ];
        foreach ($classes as $class => $icon) {
            $mc = new Megashipclass;
            $mc->name = $class;
            $mc->icon = "icons/megaships/".$icon;
            $mc->operational = (stripos($class, "damaged") !== false);
            $mc->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('megashipclasses');
    }
}
