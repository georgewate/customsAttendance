<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class AutoClockOutService
{
    /**
     * Clock out every employee who is still clocked in after the
     * configured automatic clock-out time has passed.
     *
     * Returns the number of shifts that were automatically clocked out.
     */
    public function run(): int
    {
        $setting = AttendanceSetting::current();

        if (! $setting->auto_clock_out_enabled) {
            return 0;
        }

        $today = now()->toDateString();
        $target = $setting->autoClockOutCarbon($today);

        if (now()->lt($target)) {
            return 0;
        }

        $employees = Employee::query()
            ->where('active', true)
            ->whereHas('attendanceLogs', fn ($q) => $q
                ->whereDate('scanned_at', $today)
                ->where('type', 'time_in'))
            ->whereDoesntHave('attendanceLogs', fn ($q) => $q
                ->whereDate('scanned_at', $today)
                ->where('type', 'time_out'))
            ->with(['attendanceLogs' => fn ($q) => $q
                ->whereDate('scanned_at', $today)
                ->orderBy('scanned_at')])
            ->get();

        $processed = 0;

        foreach ($employees as $employee) {
            $timeIn = $employee->attendanceLogs
                ->where('type', 'time_in')
                ->sortBy('scanned_at')
                ->last();

            if (! $timeIn) {
                continue;
            }

            $clockOutAt = $target->copy();

            if ($clockOutAt->lte($timeIn->scanned_at)) {
                $clockOutAt = now();
            }

            $autoClockedOutAt = now();

            DB::transaction(function () use ($employee, $timeIn, $clockOutAt, $autoClockedOutAt, $target, &$processed) {
                $stillOpen = AttendanceLog::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('scanned_at', now()->toDateString())
                    ->where('type', 'time_in')
                    ->latest('scanned_at')
                    ->first();

                $alreadyClosed = AttendanceLog::query()
                    ->where('employee_id', $employee->id)
                    ->whereDate('scanned_at', now()->toDateString())
                    ->where('type', 'time_out')
                    ->exists();

                if (! $stillOpen || $alreadyClosed) {
                    return;
                }

                $log = AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'location_id' => $timeIn->location_id,
                    'type' => 'time_out',
                    'scanned_at' => $clockOutAt,
                    'clock_out_type' => 'automatic',
                    'auto_clocked_out_at' => $autoClockedOutAt,
                ]);

                AuditLog::create([
                    'employee_id' => $employee->id,
                    'action' => 'automatic_clock_out',
                    'description' => "Auto clocked out {$employee->name} ({$employee->employee_code}) at {$clockOutAt->format('g:i A')}.",
                    'metadata' => [
                        'scheduled_at' => $target->toDateTimeString(),
                        'clocked_out_at' => $clockOutAt->toDateTimeString(),
                        'auto_clocked_out_at' => $autoClockedOutAt->toDateTimeString(),
                        'location_id' => $timeIn->location_id,
                        'attendance_log_id' => $log->id,
                    ],
                ]);

                $processed++;
            });
        }

        return $processed;
    }
}
