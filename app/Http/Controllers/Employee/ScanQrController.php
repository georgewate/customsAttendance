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
        $data = $request->validate([
            'token' => ['required', 'string', 'max:64'],
            'employee_code' => ['sometimes', 'required', 'string'],
            'pin' => ['sometimes', 'required', 'string'],
        ]);

        $location = Location::query()->where('qr_token', $data['token'])->first();

        if (! $location?->active) {
            return response()->json(['status' => 'error', 'message' => 'This QR code is not valid or has been deactivated.'], 404);
        }

        $employee = Auth::guard('employee')->user();

        if (! $employee) {
            if (empty($data['employee_code']) || empty($data['pin'])) {
                return response()->json(['status' => 'need_auth', 'message' => 'Sign in to clock in or out.'], 401);
            }

            $ok = Auth::guard('employee')->attempt([
                'employee_code' => $data['employee_code'],
                'password' => $data['pin'],
                'active' => true,
            ]);

            if (! $ok) {
                return response()->json(['status' => 'error', 'message' => 'Invalid employee code or PIN.'], 422);
            }

            $employee = Auth::guard('employee')->user();
            $request->session()->regenerate();
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
