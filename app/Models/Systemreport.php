<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Systemreport extends Model
{
    protected $fillable = ['date', 'system_id'];

    protected $dates = [
        'created_at',
        'updated_at',
        'date'
    ];
    
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public static function file($system, $traffic, $bounties, $crime, $username) {
        $today = Carbon::now();

        Systemreport::where('system_id', $system->id)
            ->where('current', true)
            ->update(['current' => false]);
        
        $report = Systemreport::firstOrNew([
            'date' => $today->format("Y-m-d 00:00:00"),
            'system_id' => $system->id
        ]);
        $report->traffic = (int)$traffic;
        $report->bounties = (int)$bounties;
        $report->crime = (int)$crime;
        $report->current = 1;
        $report->save();

        \Log::info("Report update", [
            'system' => $system->displayName(),
            'user' => $username
        ]);
    }
}
