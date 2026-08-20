<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Employee\AccountController;
use App\Http\Controllers\Employee\AttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\ScanQrController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanController;
use App\Models\Location;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'locations' => Location::query()->where('active', true)->orderBy('name')->get(),
    ]);
});

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('employee.auth')->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::get('/update-pin', [AccountController::class, 'showPinForm'])->name('pin');
    Route::post('/update-pin', [AccountController::class, 'updatePin'])->name('pin.update');
    Route::get('/update-profile', [AccountController::class, 'showProfileForm'])->name('profile');
    Route::post('/update-profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');
});

Route::post('/employee/scan-qr', [ScanQrController::class, 'toggle'])
    ->name('employee.scan-qr')
    ->middleware('throttle:30,1');

Route::prefix('scan/{location:qr_token}')->name('scan.')->group(function () {
    Route::get('/', [ScanController::class, 'show'])->name('show');
    Route::post('/login', [ScanController::class, 'login'])->name('login')->middleware('throttle:20,1');
    Route::post('/logout', [ScanController::class, 'logout'])->name('logout');
    Route::post('/time-in', [ScanController::class, 'timeIn'])->name('time-in')->middleware('throttle:30,1');
    Route::post('/time-out', [ScanController::class, 'timeOut'])->name('time-out')->middleware('throttle:30,1');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
