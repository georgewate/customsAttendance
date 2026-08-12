<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $latest = $employee->latestLogToday();

        return view('scan.show', [
            'location' => $location,
            'employee' => $employee,
            'latest' => $latest,
        ]);
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

        $latest = $employee->latestLogToday();

        if ($type === 'time_in' && $latest?->type === 'time_in') {
            return back()->with('error', 'Already clocked in since '.$latest->scanned_at->format('g:i A').'.');
        }

        if ($type === 'time_out' && $latest?->type !== 'time_in') {
            return back()->with('error', "You're not currently clocked in.");
        }

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => $type,
            'scanned_at' => now(),
        ]);

        $message = $type === 'time_in' ? 'Time in recorded.' : 'Time out recorded.';

        Auth::guard('employee')->logout();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', $message);
    }
}
