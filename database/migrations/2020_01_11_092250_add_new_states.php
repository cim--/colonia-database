<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\State;

class AddNewStates extends Migration
{

    private $states = [
            "Blight" => -1,
            "Terrorism" => -1,
            "Infrastructure Failure" => -1,
            "Natural Disaster" => -1,
            "Public Holiday" => 1
    ];
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->states as $name => $sign) {
            $state = new State;
            $state->name = $name;
            $state->icon = 'icons/states/'.strtolower(str_replace(' ','',$name));
            $state->sign = $sign;
            $state->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->states as $name => $sign) {
            $state->where('name', $name)->delete();
        }
    }
}
