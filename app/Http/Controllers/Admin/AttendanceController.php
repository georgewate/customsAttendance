<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dayLogs = $this->filteredQuery($request)
            ->orderBy('scanned_at')
            ->get()
            ->groupBy(fn (AttendanceLog $log) => $log->employee_id.'|'.$log->scanned_at->toDateString());

        $rows = $dayLogs->map(function ($group) {
            $sorted = $group->sortBy('scanned_at');

            return [
                'employee' => $sorted->first()->employee,
                'location' => $sorted->first()->location,
                'date' => $sorted->first()->scanned_at->copy()->startOfDay(),
                'time_in' => $sorted->firstWhere('type', 'time_in'),
                'time_out' => $sorted->where('type', 'time_out')->sortByDesc('scanned_at')->first(),
            ];
        })
            ->sortByDesc(fn (array $row) => $row['date']->toDateTimeString())
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 30;

        $logs = new LengthAwarePaginator(
            $rows->forPage($page, $perPage),
            $rows->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $locations = $this->availableLocations($request);

        return view('admin.attendance.index', [
            'logs' => $logs,
            'locations' => $locations,
            'lateReasons' => AttendanceReason::active()
                ->forCategory(AttendanceReason::CATEGORY_LATE)
                ->orderBy('name')
                ->get(),
            'earlyReasons' => AttendanceReason::active()
                ->forCategory(AttendanceReason::CATEGORY_EARLY)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, AttendanceLog $attendanceLog)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only admin officers can edit attendance records.');

        $data = $this->validateUpdateInput($request, $attendanceLog->type);

        $scannedAt = Carbon::parse($data['scanned_at']);

        $this->applyLogUpdate(
            $attendanceLog,
            $scannedAt,
            $data['late_reason_id'] ?? null,
            $data['late_other_reason'] ?? null,
            $data['early_clock_out_reason_id'] ?? null,
            $data['early_other_reason'] ?? null,
            $request->user()
        );

        return back()->with('success', 'Attendance record updated.');
    }

    public function updateDay(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403, 'Only admin officers can edit attendance records.');

        $employee = Employee::findOrFail($request->integer('employee_id'));

        $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($request->date('date'))->toDateString();

        $timeIn = $request->filled('time_in') ? Carbon::parse($request->date('time_in')) : null;
        $timeOut = $request->filled('time_out') ? Carbon::parse($request->date('time_out')) : null;

        if (! $timeIn && ! $timeOut) {
            return back()->withErrors(['time_in' => 'Provide at least one time to update.'])->withInput();
        }

        $existingIn = $employee->attendanceLogs()
            ->where('type', 'time_in')
            ->whereDate('scanned_at', $date)
            ->orderBy('scanned_at')
            ->first();

        $existingOut = $employee->attendanceLogs()
            ->where('type', 'time_out')
            ->whereDate('scanned_at', $date)
            ->orderByDesc('scanned_at')
            ->first();

        $location = $existingIn?->location ?? $existingOut?->location ?? $employee->homeLocation;

        if (! $location) {
            return back()->withErrors(['location_id' => 'No location found for this employee.'])->withInput();
        }

        if ($timeIn) {
            if ($existingIn) {
                $this->applyLogUpdate(
                    $existingIn,
                    $timeIn,
                    $request->input('time_in_late_reason_id'),
                    $request->input('time_in_late_other_reason'),
                    null,
                    null,
                    $request->user()
                );
            } else {
                $this->createLog($employee, $location, 'time_in', $timeIn, $request, $request->user());
            }
        }

        if ($timeOut) {
            if ($existingOut) {
                $this->applyLogUpdate(
                    $existingOut,
                    $timeOut,
                    null,
                    null,
                    $request->input('time_out_early_reason_id'),
                    $request->input('time_out_early_other_reason'),
                    $request->user()
                );
            } else {
                $this->createLog($employee, $location, 'time_out', $timeOut, $request, $request->user());
            }
        }

        return back()->with('success', 'Attendance record updated.');
    }

    private function createLog(Employee $employee, Location $location, string $type, Carbon $scannedAt, Request $request, User $user): void
    {
        $setting = AttendanceSetting::current();

        $isLate = $type === 'time_in' && $scannedAt->gt($setting->shiftStartCarbon($scannedAt->toDateString()));
        $isEarly = $type === 'time_out' && $scannedAt->lt($setting->shiftEndCarbon($scannedAt->toDateString()));

        $lateReasonId = $type === 'time_in' ? ($request->input('time_in_late_reason_id') ?: null) : null;
        $lateText = $type === 'time_in' ? trim($request->input('time_in_late_other_reason') ?? '') ?: null : null;
        $earlyReasonId = $type === 'time_out' ? ($request->input('time_out_early_reason_id') ?: null) : null;
        $earlyText = $type === 'time_out' ? trim($request->input('time_out_early_other_reason') ?? '') ?: null : null;

        if ($type === 'time_in' && ! $isLate) {
            $lateReasonId = null;
            $lateText = null;
        }

        if ($type === 'time_out' && ! $isEarly) {
            $earlyReasonId = null;
            $earlyText = null;
        }

        $hasReason = $lateReasonId !== null
            || $lateText !== null
            || $earlyReasonId !== null
            || $earlyText !== null;

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => $type,
            'scanned_at' => $scannedAt,
            'clock_out_type' => $type === 'time_out' ? 'manual' : null,
            'is_late' => $isLate,
            'is_early_clock_out' => $isEarly,
            'late_reason_id' => $lateReasonId,
            'late_reason_text' => $lateText,
            'early_clock_out_reason_id' => $earlyReasonId,
            'early_clock_out_reason_text' => $earlyText,
            'reason_submitted_at' => $hasReason ? now() : null,
            'reason_status' => $hasReason ? AttendanceLog::REASON_APPROVED : null,
            'reason_reviewed_by' => $hasReason ? $user->id : null,
            'reason_reviewed_at' => $hasReason ? now() : null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'employee_id' => $employee->id,
            'action' => 'attendance_log_created',
            'description' => "Created {$type} record for {$employee->name} ({$employee->employee_code}).",
            'metadata' => [
                'attendance_log_id' => $log->id,
                'type' => $type,
                'new_scanned_at' => $scannedAt->toDateTimeString(),
                'reason' => $log->reasonSummary(),
            ],
        ]);
    }

    private function validateUpdateInput(Request $request, string $type): array
    {
        $setting = AttendanceSetting::current();

        $rules = [
            'scanned_at' => ['required', 'date'],
        ];

        if ($type === 'time_in') {
            $rules['late_reason_id'] = ['nullable', 'integer', Rule::exists('attendance_reasons', 'id')
                ->where('category', AttendanceReason::CATEGORY_LATE)
                ->where('active', true)];
            $rules['late_other_reason'] = ['nullable', 'string', 'max:'.$setting->reason_text_max_length];
        } else {
            $rules['early_clock_out_reason_id'] = ['nullable', 'integer', Rule::exists('attendance_reasons', 'id')
                ->where('category', AttendanceReason::CATEGORY_EARLY)
                ->where('active', true)];
            $rules['early_other_reason'] = ['nullable', 'string', 'max:'.$setting->reason_text_max_length];
        }

        return $request->validate($rules);
    }

    private function applyLogUpdate(AttendanceLog $attendanceLog, Carbon $scannedAt, $lateReasonId, $lateText, $earlyReasonId, $earlyText, User $user): void
    {
        $setting = AttendanceSetting::current();

        $isLate = $attendanceLog->type === 'time_in'
            && $scannedAt->gt($setting->shiftStartCarbon($scannedAt->toDateString()));
        $isEarly = $attendanceLog->type === 'time_out'
            && $scannedAt->lt($setting->shiftEndCarbon($scannedAt->toDateString()));

        $lateReasonId = $attendanceLog->type === 'time_in' ? $lateReasonId : $attendanceLog->late_reason_id;
        $lateText = $attendanceLog->type === 'time_in' ? (trim($lateText ?? '') ?: null) : $attendanceLog->late_reason_text;
        $earlyReasonId = $attendanceLog->type === 'time_out' ? $earlyReasonId : $attendanceLog->early_clock_out_reason_id;
        $earlyText = $attendanceLog->type === 'time_out' ? (trim($earlyText ?? '') ?: null) : $attendanceLog->early_clock_out_reason_text;

        if ($attendanceLog->type === 'time_in' && ! $isLate) {
            $lateReasonId = null;
            $lateText = null;
        }

        if ($attendanceLog->type === 'time_out' && ! $isEarly) {
            $earlyReasonId = null;
            $earlyText = null;
        }

        $hasReason = $lateReasonId !== null
            || $lateText !== null
            || $earlyReasonId !== null
            || $earlyText !== null;

        $reasonChanged = $lateReasonId !== $attendanceLog->late_reason_id
            || $lateText !== $attendanceLog->late_reason_text
            || $earlyReasonId !== $attendanceLog->early_clock_out_reason_id
            || $earlyText !== $attendanceLog->early_clock_out_reason_text;

        $oldScannedAt = $attendanceLog->scanned_at?->toDateTimeString();
        $oldReason = $attendanceLog->reasonSummary();

        $attendanceLog->update([
            'scanned_at' => $scannedAt,
            'is_late' => $isLate,
            'is_early_clock_out' => $isEarly,
            'late_reason_id' => $lateReasonId,
            'late_reason_text' => $lateText,
            'early_clock_out_reason_id' => $earlyReasonId,
            'early_clock_out_reason_text' => $earlyText,
            'reason_submitted_at' => $hasReason ? now() : null,
            'reason_status' => $hasReason && $reasonChanged ? AttendanceLog::REASON_APPROVED : $attendanceLog->reason_status,
            'reason_reviewed_by' => $hasReason && $reasonChanged ? $user->id : $attendanceLog->reason_reviewed_by,
            'reason_reviewed_at' => $hasReason && $reasonChanged ? now() : $attendanceLog->reason_reviewed_at,
            'reason_review_note' => $hasReason && $reasonChanged ? null : $attendanceLog->reason_review_note,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'employee_id' => $attendanceLog->employee_id,
            'action' => 'attendance_log_updated',
            'description' => "Updated attendance record for {$attendanceLog->employee?->name} ({$attendanceLog->employee?->employee_code}).",
            'metadata' => [
                'attendance_log_id' => $attendanceLog->id,
                'type' => $attendanceLog->type,
                'old_scanned_at' => $oldScannedAt,
                'new_scanned_at' => $scannedAt->toDateTimeString(),
                'old_reason' => $oldReason,
                'new_reason' => $attendanceLog->reasonSummary(),
            ],
        ]);
    }

    public function approveReason(Request $request, AttendanceLog $attendanceLog)
    {
        abort_unless($attendanceLog->reasonIsPending(), 409, 'This record has no pending reason to approve.');

        $attendanceLog->update([
            'reason_status' => AttendanceLog::REASON_APPROVED,
            'reason_reviewed_by' => $request->user()->id,
            'reason_reviewed_at' => now(),
            'reason_review_note' => null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'employee_id' => $attendanceLog->employee_id,
            'action' => 'attendance_reason_approved',
            'description' => "Approved attendance reason for {$attendanceLog->employee?->name} ({$attendanceLog->employee?->employee_code}).",
            'metadata' => [
                'attendance_log_id' => $attendanceLog->id,
                'reason' => $attendanceLog->reasonSummary(),
            ],
        ]);

        return back()->with('success', 'Reason approved.');
    }

    public function rejectReason(Request $request, AttendanceLog $attendanceLog)
    {
        abort_unless($attendanceLog->reasonIsPending(), 409, 'This record has no pending reason to reject.');

        $data = $request->validate([
            'reason_review_note' => ['nullable', 'string', 'max:255'],
        ]);

        $attendanceLog->update([
            'reason_status' => AttendanceLog::REASON_REJECTED,
            'reason_reviewed_by' => $request->user()->id,
            'reason_reviewed_at' => now(),
            'reason_review_note' => $data['reason_review_note'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'employee_id' => $attendanceLog->employee_id,
            'action' => 'attendance_reason_rejected',
            'description' => "Rejected attendance reason for {$attendanceLog->employee?->name} ({$attendanceLog->employee?->employee_code}).",
            'metadata' => [
                'attendance_log_id' => $attendanceLog->id,
                'reason' => $attendanceLog->reasonSummary(),
                'note' => $attendanceLog->reason_review_note,
            ],
        ]);

        return back()->with('success', 'Reason rejected.');
    }

    public function export(Request $request): StreamedResponse
    {
        $logs = $this->filteredQuery($request)
            ->with(['lateReason', 'earlyClockOutReason'])
            ->orderBy('scanned_at')
            ->get();

        return response()->streamDownload(function () use ($logs) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Employee code',
                'Employee name',
                'Location',
                'Type',
                'Clock-out type',
                'Scanned at',
                'Late',
                'Early clock-out',
                'Late reason',
                'Late custom reason',
                'Early reason',
                'Early custom reason',
            ]);

            foreach ($logs as $log) {
                fputcsv($out, [
                    $log->employee->employee_code,
                    $log->employee->name,
                    $log->location->name,
                    $log->type,
                    $log->type === 'time_out'
                        ? ucfirst($log->clock_out_type ?? 'manual')
                        : '',
                    $log->scanned_at->toDateTimeString(),
                    $log->is_late ? 'Yes' : 'No',
                    $log->is_early_clock_out ? 'Yes' : 'No',
                    $log->lateReason?->name ?? '',
                    $log->late_reason_text ?? '',
                    $log->earlyClockOutReason?->name ?? '',
                    $log->early_clock_out_reason_text ?? '',
                ]);
            }

            fclose($out);
        }, 'attendance.csv');
    }

    private function filteredQuery(Request $request)
    {
        $user = $request->user();

        $query = AttendanceLog::query()->with(['employee', 'location', 'lateReason', 'earlyClockOutReason']);

        if (! $user->isAdmin()) {
            $query->where('location_id', $user->location_id);
        } elseif ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->integer('employee_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('scanned_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('scanned_at', '<=', $request->date('to'));
        }

        return $query;
    }

    private function availableLocations(Request $request)
    {
        $user = $request->user();

        return $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();
    }
}
