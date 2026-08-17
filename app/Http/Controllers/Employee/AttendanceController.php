<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    private const MAX_RANGE_DAYS = 90;

    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        $from = $this->parseDate($request->input('from')) ?? today()->subDays(6);
        $to = $this->parseDate($request->input('to')) ?? today();

        if ($from->gt($to)) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        if ($from->diffInDays($to) >= self::MAX_RANGE_DAYS) {
            $from = $to->copy()->subDays(self::MAX_RANGE_DAYS - 1);
        }

        $setting = AttendanceSetting::current();

        $logs = $employee->attendanceLogs()
            ->with(['location', 'lateReason', 'earlyClockOutReason'])
            ->whereDate('scanned_at', '>=', $from)
            ->whereDate('scanned_at', '<=', $to)
            ->orderBy('scanned_at')
            ->get()
            ->groupBy(fn ($log) => $log->scanned_at->toDateString());

        $records = $this->buildRecords($logs, $from, $to, $setting);

        return view('employee.attendance', [
            'employee' => $employee,
            'records' => $records,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'maxRangeDays' => self::MAX_RANGE_DAYS,
            'shiftStart' => $setting->shift_start_time,
            'shiftEnd' => $setting->shift_end_time,
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! $value || ! strtotime($value)) {
            return null;
        }

        return Carbon::parse($value);
    }

    private function buildRecords($logs, Carbon $from, Carbon $to, AttendanceSetting $setting): array
    {
        $records = [];
        $today = today();
        $day = $from->copy();

        while ($day->lte($to) && $day->lte($today)) {
            $key = $day->toDateString();
            $dayLogs = $logs->get($key, collect());

            $timeIn = $dayLogs->where('type', 'time_in')->sortBy('scanned_at')->first();
            $timeOut = $dayLogs->where('type', 'time_out')->sortByDesc('scanned_at')->first();

            if (! $timeIn) {
                $records[] = [
                    'date' => $day->copy(),
                    'time_in' => null,
                    'time_out' => null,
                    'clock_out_type' => null,
                    'hours' => null,
                    'status' => 'absent',
                    'status_label' => 'Absent',
                    'location' => null,
                    'is_late' => false,
                    'is_early_clock_out' => false,
                    'late_reason' => null,
                    'late_reason_text' => null,
                    'early_reason' => null,
                    'early_reason_text' => null,
                ];
                $day->addDay();

                continue;
            }

            $isLate = $timeIn->scanned_at->gt($setting->shiftStartCarbon($day->toDateString()));
            $isEarly = $timeOut
                ? $timeOut->scanned_at->lt($setting->shiftEndCarbon($day->toDateString()))
                : false;

            if ($timeOut) {
                $hours = $this->formatHours($timeIn->scanned_at->diffInMinutes($timeOut->scanned_at));
                $status = $isLate ? 'late' : 'present';
                $statusLabel = $isLate ? 'Late' : 'Present';
            } else {
                $hours = $this->formatHours($timeIn->scanned_at->diffInMinutes(now()));
                $status = $isLate ? 'late' : 'present';
                $statusLabel = $isLate ? 'Late · On clock' : 'Present · On clock';
            }

            $records[] = [
                'date' => $day->copy(),
                'time_in' => $timeIn->scanned_at,
                'time_out' => $timeOut?->scanned_at,
                'clock_out_type' => $timeOut
                    ? ($timeOut->clock_out_type ?? 'manual')
                    : null,
                'hours' => $hours,
                'status' => $status,
                'status_label' => $statusLabel,
                'location' => $timeIn->location?->name ?? $timeOut?->location?->name,
                'is_late' => $isLate,
                'is_early_clock_out' => $isEarly,
                'late_reason' => $timeIn->lateReason?->name,
                'late_reason_text' => $timeIn->late_reason_text,
                'early_reason' => $timeOut?->earlyClockOutReason?->name,
                'early_reason_text' => $timeOut?->early_clock_out_reason_text,
            ];

            $day->addDay();
        }

        return $records;
    }

    private function formatHours(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0 ? "{$hours}h {$remaining}m" : "{$remaining}m";
    }
}
