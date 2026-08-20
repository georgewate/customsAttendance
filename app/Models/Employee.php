<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['employee_code', 'name', 'email', 'photo', 'department', 'pin_hash', 'home_location_id', 'active'])]
#[Hidden(['pin_hash', 'remember_token'])]
class Employee extends Authenticatable
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->pin_hash;
    }

    public function homeLocation()
    {
        return $this->belongsTo(Location::class, 'home_location_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function latestLogToday(?string $timezone = null): ?AttendanceLog
    {
        $now = now($timezone);

        return $this->attendanceLogs()
            ->whereDate('scanned_at', $now->toDateString())
            ->where('scanned_at', '<=', $now)
            ->latest('scanned_at')
            ->first();
    }

    public function clockedInToday(?string $timezone = null): bool
    {
        $now = now($timezone);

        return $this->attendanceLogs()
            ->where('type', 'time_in')
            ->whereDate('scanned_at', $now->toDateString())
            ->exists();
    }

    public function clockedOutToday(?string $timezone = null): bool
    {
        $now = now($timezone);

        return $this->attendanceLogs()
            ->where('type', 'time_out')
            ->whereDate('scanned_at', $now->toDateString())
            ->exists();
    }
}
