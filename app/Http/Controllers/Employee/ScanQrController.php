<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\AttendanceSetting;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScanQrController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $employee = Auth::guard('employee')->user();

        if (! $employee) {
            return response()->json(['status' => 'error', 'message' => 'Your session has expired. Please sign in again.'], 403);
        }

        $data = $request->validate([
            'token' => ['required', 'string', 'max:64'],
        ]);

        $location = Location::query()->where('qr_token', $data['token'])->first();

        if (! $location?->active) {
            return response()->json(['status' => 'error', 'message' => 'This QR code is not valid or has been deactivated.'], 404);
        }

        if ($employee->clockedOutToday()) {
            return response()->json(['status' => 'error', 'message' => "You've already clocked out today."], 422);
        }

        $type = $employee->clockedInToday() ? 'time_out' : 'time_in';

        $setting = AttendanceSetting::current();
        $scannedAt = now();

        $log = AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => $type,
            'scanned_at' => $scannedAt,
            'clock_out_type' => $type === 'time_out' ? 'manual' : null,
            'is_late' => $type === 'time_in' && $scannedAt->gt($setting->shiftStartCarbon()),
            'is_early_clock_out' => $type === 'time_out' && $scannedAt->lt($setting->shiftEndCarbon()),
        ]);

        $action = $type === 'time_in' ? 'clock_in' : 'clock_out';
        $message = $type === 'time_in' ? 'Clocked in at '.$scannedAt->format('g:i A').'.' : 'Clocked out at '.$scannedAt->format('g:i A').'.';

        return response()->json([
            'status' => 'ok',
            'action' => $action,
            'message' => $message.' Location: '.$location->name.'.',
            'time' => $scannedAt->format('g:i A'),
            'location' => $location->name,
            'log_id' => $log->id,
        ]);
    }
}
