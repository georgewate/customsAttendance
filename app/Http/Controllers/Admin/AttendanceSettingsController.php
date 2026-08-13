<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSetting;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AttendanceSettingsController extends Controller
{
    public function index(): View
    {
        $setting = AttendanceSetting::current()->load('updater');

        return view('admin.attendance.settings', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = AttendanceSetting::current();

        $data = Validator::make($request->all(), [
            'auto_clock_out_time' => ['required', 'string', 'date_format:H:i'],
            'auto_clock_out_enabled' => ['sometimes', 'boolean'],
            'shift_start_time' => ['required', 'string', 'date_format:H:i'],
            'shift_end_time' => ['required', 'string', 'date_format:H:i'],
            'reason_text_max_length' => ['required', 'integer', 'min:10', 'max:2000'],
        ])->validate();

        $enabled = $request->boolean('auto_clock_out_enabled');

        $old = $setting->only([
            'auto_clock_out_time',
            'auto_clock_out_enabled',
            'shift_start_time',
            'shift_end_time',
            'reason_text_max_length',
        ]);

        $setting->update([
            'auto_clock_out_time' => $data['auto_clock_out_time'],
            'auto_clock_out_enabled' => $enabled,
            'shift_start_time' => $data['shift_start_time'],
            'shift_end_time' => $data['shift_end_time'],
            'reason_text_max_length' => $data['reason_text_max_length'],
            'updated_by' => Auth::id(),
        ]);

        $new = $setting->only(array_keys($old));

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'attendance_settings_updated',
            'description' => 'Attendance settings updated.',
            'metadata' => [
                'old' => $old,
                'new' => $new,
            ],
        ]);

        return redirect()
            ->route('admin.attendance.settings')
            ->with('success', 'Attendance settings saved.');
    }
}
