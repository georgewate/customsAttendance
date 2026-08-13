<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        $latest = $employee->latestLogToday();

        $todayLogs = $employee->attendanceLogs()
            ->with('location')
            ->whereDate('scanned_at', today())
            ->latest('scanned_at')
            ->get();

        $weekLogs = $employee->attendanceLogs()
            ->whereDate('scanned_at', '>=', today()->startOfWeek())
            ->get();

        $daysPresentThisWeek = $weekLogs
            ->groupBy(fn ($log) => $log->scanned_at->toDateString())
            ->count();

        $monthCount = $employee->attendanceLogs()
            ->whereMonth('scanned_at', now()->month)
            ->whereYear('scanned_at', now()->year)
            ->count();

        $recentLogs = $employee->attendanceLogs()
            ->with('location')
            ->latest('scanned_at')
            ->limit(8)
            ->get();

        return view('employee.dashboard', [
            'employee' => $employee,
            'latest' => $latest,
            'todayLogs' => $todayLogs,
            'daysPresentThisWeek' => $daysPresentThisWeek,
            'monthCount' => $monthCount,
            'recentLogs' => $recentLogs,
        ]);
    }
}
