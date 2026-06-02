<?php

namespace App\Services;

use App\Models\AcademicCalendarHoliday;
use App\Models\School;
use App\Support\TenantContext;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AttendanceCalendarService
{
    public function holidayFor(string|CarbonInterface $date): ?AcademicCalendarHoliday
    {
        return AcademicCalendarHoliday::query()
            ->whereDate('date', $this->dateString($date))
            ->where('status', 'active')
            ->first();
    }

    public function isHoliday(string|CarbonInterface $date): bool
    {
        return (bool) $this->holidayFor($date);
    }

    public function hasLateCutoffPassed(string|CarbonInterface $date): bool
    {
        $date = Carbon::parse($this->dateString($date));
        $today = today();

        if ($date->lt($today)) {
            return true;
        }

        if ($date->gt($today)) {
            return false;
        }

        $school = TenantContext::school();
        $lateAfter = $school?->student_attendance_late_after;

        if (blank($lateAfter)) {
            return false;
        }

        return now()->gt(now()->copy()->setTimeFromTimeString((string) $lateAfter));
    }

    private function dateString(string|CarbonInterface $date): string
    {
        return $date instanceof CarbonInterface ? $date->toDateString() : $date;
    }
}
