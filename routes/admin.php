<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AttendanceReasonController;
use App\Http\Controllers\Admin\AttendanceSettingsController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('employees', EmployeeController::class)->except('show');
        Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
        Route::post('attendance/day', [AttendanceController::class, 'updateDay'])->name('attendance.update-day');
        Route::post('attendance/{attendanceLog}/approve-reason', [AttendanceController::class, 'approveReason'])->name('attendance.approve-reason');
        Route::post('attendance/{attendanceLog}/reject-reason', [AttendanceController::class, 'rejectReason'])->name('attendance.reject-reason');
        Route::get('attendance/reports', [ReportController::class, 'index'])->name('attendance.reports');
        Route::get('attendance/reports/download', [ReportController::class, 'download'])->name('attendance.reports.download');
        Route::get('attendance/reports/detailed', [ReportController::class, 'detailed'])->name('attendance.reports.detailed');
        Route::get('attendance/reports/detailed/download', [ReportController::class, 'detailedDownload'])->name('attendance.reports.detailed.download');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('attendance/settings', [AttendanceSettingsController::class, 'index'])->name('attendance.settings');
        Route::post('attendance/settings', [AttendanceSettingsController::class, 'update'])->name('attendance.settings.update');
        Route::patch('attendance/{attendanceLog}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::resource('attendance-reasons', AttendanceReasonController::class)->except('show');
        Route::patch('attendance-reasons/{attendanceReason}/toggle', [AttendanceReasonController::class, 'toggle'])->name('attendance-reasons.toggle');
        Route::resource('locations', LocationController::class);
        Route::get('locations/{location}/qr', [LocationController::class, 'qr'])->name('locations.qr');
        Route::post('locations/{location}/regenerate-token', [LocationController::class, 'regenerateToken'])->name('locations.regenerate-token');
        Route::resource('users', UserController::class)->except('show');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('settings/api-docs/pdf', [SettingsController::class, 'apiDocsPdf'])->name('settings.api-docs.pdf');
        Route::get('settings/user-guide/pdf', [SettingsController::class, 'userGuidePdf'])->name('settings.user-guide.pdf');
        Route::get('settings/technical-docs/pdf', [SettingsController::class, 'technicalDocsPdf'])->name('settings.technical-docs.pdf');
    });
});
