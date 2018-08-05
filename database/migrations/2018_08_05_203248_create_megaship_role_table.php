<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Megashiprole;

class CreateMegashipRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('megashiproles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        $roles = [
            "Logistics" => "This ship transports supplies around the Colonia region.",
            "Resupply" => "This ship transports goods from Colonia to keep the highway bases operational.",
            "Trade" => "This ship trades between Colonia and a system in the Sol bubble.",
            "Unknown" => "The purpose of this ship is not recorded."
        ];
        foreach ($roles as $name => $desc) {
            $role = new Megashiprole;
            $role->name = $name;
            $role->description = $desc;
            $role->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('megashiproles');
    }
}
