<?php

namespace App\Services;

use App\Models\AttendanceLog;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DeduplicateAttendanceLogsService
{
    /**
     * Enforce the one-clock-in / one-clock-out-per-day rule for existing
     * records. For every employee and day the earliest clock-in and the
     * latest clock-out are kept; any additional records are removed.
     *
     * Returns the number of logs deleted.
     */
    public function run(): int
    {
        $deleted = 0;

        DB::transaction(function () use (&$deleted) {
            $deleted += $this->deduplicate('time_in', 'asc');
            $deleted += $this->deduplicate('time_out', 'desc');
        });

        if ($deleted > 0) {
            AuditLog::create([
                'action' => 'attendance_logs_deduplicated',
                'description' => "Removed {$deleted} duplicate attendance log(s) so each employee has a single clock-in and clock-out per day.",
                'metadata' => [
                    'deleted_count' => $deleted,
                ],
            ]);
        }

        return $deleted;
    }

    private function deduplicate(string $type, string $direction): int
    {
        $deleted = 0;

        AttendanceLog::query()
            ->where('type', $type)
            ->get()
            ->groupBy(fn (AttendanceLog $log) => $log->employee_id.'|'.$log->scanned_at->toDateString())
            ->filter(fn ($group) => $group->count() > 1)
            ->each(function ($group) use ($direction, &$deleted) {
                $keep = $direction === 'asc' ? $group->sortBy('scanned_at')->first() : $group->sortByDesc('scanned_at')->first();

                $group->reject(fn (AttendanceLog $log) => $log->id === $keep->id)
                    ->each(fn (AttendanceLog $log) => $log->delete());

                $deleted += $group->count() - 1;
            });

        return $deleted;
    }
}
