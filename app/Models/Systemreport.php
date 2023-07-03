<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Eddnevent;

class Systemreport extends Model
{
    protected $fillable = ['date', 'system_id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date' => 'datetime'
    ];
    
    public function system() {
        return $this->belongsTo('App\Models\System');
    }

    public static function file($system, $traffic, $bounties, $crime, $username, $estimated=false, $today=null) {
        $limit1 = Carbon::now();
        $limit2 = Carbon::now();
        $limit1->subDay();
        $limit1->minute = 0;
        $limit1->second = 0;
        $limit2->minute = 0;
        $limit2->second = 0;
        $current = false;
        
        if ($today === null) {
            if (!$estimated) {
                $today = Carbon::now();
                $current = true;
                Systemreport::where('system_id', $system->id)
                    ->where('current', true)
                    ->update(['current' => false]);
            } else {
                $today = new Carbon('yesterday');
            }
        }
       
        $report = Systemreport::firstOrNew([
            'date' => $today->format("Y-m-d 00:00:00"),
            'system_id' => $system->id
        ]);
        $report->traffic = (int)$traffic;
        $report->bounties = (int)$bounties;
        $report->crime = (int)$crime;

        if ($estimated) {
            $report->estimated = true;
            $report->current = 0;
        } else if ($current) {
            /* The in-game traffic report is updated hourly, so match the
             * eddnevent querying to hour-aligned windows. This will
             * probably give slightly skewed results over timezone
             * changes, but it's not worth worrying about. */
            $eddncount = Eddnevent::where('system_id', $system->id)
                ->where('eventtime', '>=', $limit1)
                ->where('eventtime', '<', $limit2)
                ->count();

            $report->estimated = false;
            $report->eddncount = $eddncount;
            $report->current = 1;
        } else {
            $report->estimated = false;
            $report->current = 0;
        }
        
        $report->save();

        \Log::info("Report update", [
            'system' => $system->displayName(),
            'user' => $username
        ]);
    }
}
