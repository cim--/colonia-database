<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\History;

class UpdateRecordingInformation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('historys', function (Blueprint $table) {
            $table->string('description')->nullable();
        });
        History::where('location_type', 'App\Models\System')
            ->where('expansion', true)
            ->update(['description' => 'expanded to']);
        History::where('location_type', 'App\Models\System')
            ->where('expansion', false)
            ->update(['description' => 'retreated from']);
        History::where('location_type', 'App\Models\Station')
            ->where('expansion', true)
            ->update(['description' => 'took control of']);
        History::where('location_type', 'App\Models\Station')
            ->where('expansion', false)
            ->update(['description' => 'lost control of']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('historys', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
