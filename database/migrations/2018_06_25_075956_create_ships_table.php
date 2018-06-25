<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Ship;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('eddn');
            $table->timestamps();
        });

        $ships = [
            "SideWinder" => "Sidewinder",
            "Eagle" => "Eagle",
            "Hauler" => "Hauler",
            "Adder" => "Adder",
            "Viper" => "Viper MkIII",
            "CobraMkIII" => "Cobra MkIII",
            "Type6" => "Type-6 Transporter",
            "Dolphin" => "Dolphin",
            "Type7" => "Type-7 Transporter",
            "Asp" => "Asp Explorer",
            "Vulture" => "Vulture",
            "Empire_Trader" => "Imperial Clipper",
            "Federation_Dropship" => "Federal Dropship",
            "Orca" => "Orca",
            "Type9" => "Type-9 Heavy",
            "Python" => "Python",
            "BelugaLiner" => "Beluga Liner",
            "FerDeLance" => "Fer-de-Lance",
            "Anaconda" => "Anaconda",
            "Federation_Corvette" => "Federal Corvette",
            "Cutter" => "Imperial Cutter",
            "DiamondBack" => "Diamondback Scout",
            "Empire_Courier" => "Imperial Courier",
            "DiamondBackXL" => "Diamondback Explorer",
            "Empire_Eagle" => "Imperial Eagle",
            "Federation_Dropship_MkII" => "Federal Assault Ship",
            "Federation_Gunship" => "Federal Gunship",
            "Viper_MkIV" => "Viper MkIV",
            "CobraMkIV" => "Cobra MkIV",
            "Independant_Trader" => "Keelback",
            "Asp_Scout" => "Asp Scout",
            "Type9_Military" => "Type-10 Defender",
            "TypeX" => "Alliance Chieftain"
        ];
        foreach ($ships as $eddn => $name) {
            $ship = new Ship;
            $ship->name = $name;
            $ship->eddn = $eddn;
            $ship->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ships');
    }
}
