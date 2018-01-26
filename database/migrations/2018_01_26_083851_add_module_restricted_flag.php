<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Module;

class AddModuleRestrictedFlag extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('restricted')->default(false);
        });

        Module::where('eddn', 'LIKE', '%BuggyBay%')
            ->orWhere('eddn', 'LIKE', '%CorrosionProof%')
            ->orWhere('eddn', 'LIKE', '%FighterBay%')
            ->orWhere('eddn', 'LIKE', '%AntiUnknownShutdown%')
            ->orWhere('eddn', 'LIKE', '%Type9_Military%')
            ->orWhere('eddn', 'LIKE', '%CobraMkIV%')
            ->orWhere('eddn', 'LIKE', '%ATDumb%')
            ->orWhere('eddn', 'LIKE', '%ATMulti%')
            ->orWhere('eddn', 'LIKE', '%FlakMortar%')
            ->orWhere('eddn', 'LIKE', '%XenoScanner%')
            ->orWhere('eddn', 'LIKE', '%Decontamination%')
            ->update(['restricted' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('restricted');
        });
    }
}
