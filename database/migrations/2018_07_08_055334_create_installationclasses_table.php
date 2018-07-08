<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Installationclass;

class CreateInstallationclassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installationclasses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('icon');
            $table->timestamps();
        });

        $classes = [
            "Civilian", "Agricultural", "Industrial", "Tourist",
            "Military", "Security", "Medical", "Communications",
            "Scientific", "Unauthorised"
        ];
        foreach ($classes as $class) {
            $ic = new Installationclass;
            $ic->name = $class;
            $ic->icon = 'icons/installations/'.strtolower($class);
            $ic->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installationclasses');
    }
}
