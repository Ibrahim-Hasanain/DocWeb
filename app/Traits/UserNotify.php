<?php

namespace App\Traits;

use App\Constants\Status;

trait UserNotify
{


    public static function notifyToDoctor()
    {
        return [
            'allDoctors'              => 'All Doctors',
            'selectedDoctors'         => 'Selected Doctors',
            'withBalance'           => 'With Balance Doctors',
            'emptyBalanceDoctors'     => 'Empty Balance Doctors',
        ];
    }


    public static function notifyToAssistant()
    {
        return [
            'allAssistants'              => 'All Assistant',
            'selectedAssistants'         => 'Selected Assistant',
        ];
    }



    public static function notifyToStaff()
    {
        return [
            'allStaffs'              => 'All Staff',
            'selectedStaffs'         => 'Selected Staff',
        ];
    }


    public function scopeSelectedDoctors($query)
    {
        return $query->whereIn('id', request()->doctor ?? []);
    }


    public function scopeSelectedAssistants($query)
    {
        return $query->whereIn('id', request()->assistant ?? []);
    }

    public function scopeSelectedStaffs($query)
    {
        return $query->whereIn('id', request()->staff ?? []);
    }

    public function scopeAllDoctors($query)
    {
        return $query;
    }

    public function scopeAllAssistants($query)
    {
        return $query;
    }

    public function scopeAllStaffs($query)
    {
        return $query;
    }

    public function scopeEmptyBalanceDoctors($query)
    {
        return $query->where('balance', '<=', 0);
    }


    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }
}
