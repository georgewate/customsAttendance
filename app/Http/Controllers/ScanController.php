<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ScanController extends Controller
{
    public function show(Location $location)
    {
        abort_unless($location->active, 404);

        $employee = Auth::guard('employee')->user();

        if (! $employee) {
            return view('scan.login', compact('location'));
        }

        $setting = AttendanceSetting::current();
        $latest = $employee->latestLogToday();

        return view('scan.show', [
            'location' => $location,
            'employee' => $employee,
            'latest' => $latest,
            'setting' => $setting,
            'todayLogs' => $employee->attendanceLogs()
                ->with('location')
                ->whereDate('scanned_at', today())
                ->where('scanned_at', '<=', now())
                ->orderBy('scanned_at')
                ->get(),
            'history' => $this->historyPreview($employee, $setting),
            'lateReasons' => AttendanceReason::active()
                ->forCategory(AttendanceReason::CATEGORY_LATE)
                ->orderBy('name')
                ->get(),
            'earlyReasons' => AttendanceReason::active()
                ->forCategory(AttendanceReason::CATEGORY_EARLY)
                ->orderBy('name')
                ->get(),
            'isLateNow' => now()->gt($setting->shiftStartCarbon()),
            'isEarlyNow' => now()->lt($setting->shiftEndCarbon()),
            'reasonMaxLength' => $setting->reason_text_max_length,
        ]);
    }

    private function historyPreview(Employee $employee, AttendanceSetting $setting): array
    {
        $from = today()->subDays(6);

        $logs = $employee->attendanceLogs()
            ->whereDate('scanned_at', '>=', $from)
            ->where('scanned_at', '<=', now())
            ->orderBy('scanned_at')
            ->get()
            ->groupBy(fn ($log) => $log->scanned_at->toDateString());

        $records = [];
        $day = $from->copy();

        while ($day->lte(today())) {
            $key = $day->toDateString();
            $dayLogs = $logs->get($key, collect());

            $timeIn = $dayLogs->where('type', 'time_in')->sortBy('scanned_at')->first();
            $timeOut = $dayLogs->where('type', 'time_out')->sortByDesc('scanned_at')->first();

            if (! $timeIn) {
                $day->addDay();

                continue;
            }

            $records[] = [
                'date' => $day->copy(),
                'time_in' => $timeIn->scanned_at,
                'time_out' => $timeOut?->scanned_at,
                'clock_out_type' => $timeOut ? ($timeOut->clock_out_type ?? 'manual') : null,
                'hours' => $this->formatHours($timeIn->scanned_at->diffInMinutes($timeOut?->scanned_at ?? now())),
                'is_late' => $timeIn->scanned_at->gt($setting->shiftStartCarbon($key)),
                'on_clock' => $timeOut === null,
            ];

            $day->addDay();
        }

        return array_reverse($records);
    }

    private function formatHours(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $hours > 0 ? "{$hours}h {$remaining}m" : "{$remaining}m";
    }

    public function login(Request $request, Location $location)
    {
        abort_unless($location->active, 404);

        $credentials = $request->validate([
            'employee_code' => ['required', 'string'],
            'pin' => ['required', 'string'],
        ]);

        $ok = Auth::guard('employee')->attempt([
            'employee_code' => $credentials['employee_code'],
            'password' => $credentials['pin'],
            'active' => true,
        ]);

        if (! $ok) {
            throw ValidationException::withMessages([
                'employee_code' => 'Invalid employee code or PIN.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('scan.show', $location->qr_token);
    }

    public function logout(Request $request, Location $location)
    {
        Auth::guard('employee')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('scan.show', $location->qr_token);
    }

    public function timeIn(Request $request, Location $location)
    {
        return $this->recordScan($request, $location, 'time_in');
    }

    public function timeOut(Request $request, Location $location)
    {
        return $this->recordScan($request, $location, 'time_out');
    }

    private function recordScan(Request $request, Location $location, string $type)
    {
        abort_unless($location->active, 404);

        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        if ($type === 'time_in' && $employee->clockedInToday()) {
            return back()->with('error', "You've already clocked in today.");
        }

        if ($type === 'time_out') {
            if ($employee->clockedOutToday()) {
                return back()->with('error', "You've already clocked out today.");
            }

            if (! $employee->clockedInToday()) {
                return back()->with('error', "You're not currently clocked in.");
            }
        }

        $setting = AttendanceSetting::current();
        $scannedAt = now();
        $isLate = $type === 'time_in' && $scannedAt->gt($setting->shiftStartCarbon());
        $isEarly = $type === 'time_out' && $scannedAt->lt($setting->shiftEndCarbon());

        $data = $this->validateReasonInput($request, $type, $isLate, $isEarly, $setting);

        $hasLateReason = ! empty($data['late_reason_id'] ?? null) || ! empty($data['late_other_reason'] ?? null);
        $hasEarlyReason = ! empty($data['early_clock_out_reason_id'] ?? null) || ! empty($data['early_other_reason'] ?? null);
        $hasReason = $hasLateReason || $hasEarlyReason;

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => $type,
            'scanned_at' => $scannedAt,
            'clock_out_type' => $type === 'time_out' ? 'manual' : null,
            'is_late' => $isLate,
            'is_early_clock_out' => $isEarly,
            'late_reason_id' => $data['late_reason_id'] ?? null,
            'late_reason_text' => $data['late_other_reason'] ?? null,
            'early_clock_out_reason_id' => $data['early_clock_out_reason_id'] ?? null,
            'early_clock_out_reason_text' => $data['early_other_reason'] ?? null,
            'reason_submitted_at' => $hasReason ? $scannedAt : null,
            'reason_status' => $hasReason ? AttendanceLog::REASON_PENDING : null,
        ]);

        if ($hasReason) {
            AuditLog::create([
                'employee_id' => $employee->id,
                'action' => 'attendance_reason_submitted',
                'description' => ($isLate ? 'Late arrival' : 'Early clock-out')." reason submitted by {$employee->name} ({$employee->employee_code}).",
                'metadata' => [
                    'attendance_log_id' => $log->id,
                    'type' => $type,
                    'is_late' => $isLate,
                    'is_early_clock_out' => $isEarly,
                    'late_reason_id' => $data['late_reason_id'] ?? null,
                    'late_reason_text' => $data['late_other_reason'] ?? null,
                    'early_clock_out_reason_id' => $data['early_clock_out_reason_id'] ?? null,
                    'early_clock_out_reason_text' => $data['early_other_reason'] ?? null,
                    'submitted_at' => $scannedAt->toDateTimeString(),
                ],
            ]);
        }

        $message = $type === 'time_in' ? 'Time in recorded.' : 'Time out recorded.';

        return back()->with('success', $message);
    }

    private function validateReasonInput(Request $request, string $type, bool $isLate, bool $isEarly, AttendanceSetting $setting): array
    {
        $rules = [];
        $messages = [];

        if ($isLate) {
            $rules['late_reason_id'] = ['nullable', 'integer', Rule::exists('attendance_reasons', 'id')
                ->where('category', AttendanceReason::CATEGORY_LATE)
                ->where('active', true)];
            $rules['late_other_reason'] = ['nullable', 'string', 'max:'.$setting->reason_text_max_length];
            $messages['late_reason_id.exists'] = 'The selected late arrival reason is no longer available.';
            $messages['late_other_reason.max'] = 'The custom reason must not be longer than :max characters.';
        }

        if ($isEarly) {
            $rules['early_clock_out_reason_id'] = ['nullable', 'integer', Rule::exists('attendance_reasons', 'id')
                ->where('category', AttendanceReason::CATEGORY_EARLY)
                ->where('active', true)];
            $rules['early_other_reason'] = ['nullable', 'string', 'max:'.$setting->reason_text_max_length];
            $messages['early_clock_out_reason_id.exists'] = 'The selected early clock-out reason is no longer available.';
            $messages['early_other_reason.max'] = 'The custom reason must not be longer than :max characters.';
        }

        $validator = validator($request->all(), $rules, $messages);

        return $validator->validate();
    }
}
