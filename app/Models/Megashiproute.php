<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Megashiproute extends Model
{
    public function megaship()
    {
        return $this->belongsTo('App\Models\Megaship');
    }

    public function system()
    {
        return $this->belongsTo('App\Models\System');
    }

    private function lastThursday()
    {
        // if it is Thursday, return today
        $now = Carbon::now();
        if ($now->dayOfWeek == 4) {
            return $now;
        } else if ($now->dayOfWeek > 4) {
            return $now->subDays($now->dayOfWeek - 4);
        } else {
            return $now->subDays($now->dayOfWeek + 3);
        }
    }
    
    public function nextArrival()
    {
        $current = $this->megaship->currentSequence()->sequence;
        if ($current == $this->sequence) {
            return null; // has arrived
        }
        $count = $this->megaship->sequenceCount();
        $date = $this->lastThursday();
        do {
            $date->addWeek();
            $current++;
            if ($current == $count) {
                $current = 0;
            }
        } while ($current != $this->sequence);
        return $date;
    }

    public function nextDeparture()
    {
        $current = $this->megaship->currentSequence()->sequence;
        if ($current == $this->sequence) {
            $date = $this->lastThursday();
        } else {
            $date = $this->nextArrival();
        }
        return $date->addWeek();
    }
}
