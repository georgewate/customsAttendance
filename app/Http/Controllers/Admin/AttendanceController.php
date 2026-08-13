<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filteredQuery($request)
            ->latest('scanned_at')
            ->paginate(30)
            ->withQueryString();

        $locations = $this->availableLocations($request);

        return view('admin.attendance.index', compact('logs', 'locations'));
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
