<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Ethos;

class AddEthosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ethoses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('adminname');
            $table->timestamps();
        });

        $create = [
            "Social" => "Social",
            "Corporate" => "Corporate",
            "Authoritarian" => "Authoritarian",
            "Criminal" => "Criminal",
            "Unknown" => "Unknown",
            "CorA 1" => "Unknown",
            "CorA 2" => "Unknown",
            "CorA 3" => "Unknown",
            "CorA 4" => "Unknown",
            "CorA 5" => "Unknown",
            "CorA 6" => "Unknown"
        ];
        foreach ($create as $admin => $name) {
            $ethos = new Ethos;
            $ethos->name = $name;
            $ethos->adminname = $admin;
            $ethos->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ethoses');
    }
}
