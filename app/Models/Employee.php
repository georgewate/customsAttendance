<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Fillable(['employee_code', 'name', 'email', 'photo', 'pin_hash', 'home_location_id', 'active'])]
#[Hidden(['pin_hash', 'remember_token'])]
class Employee extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
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
        return $this->attendanceLogs()
            ->whereDate('scanned_at', now($timezone)->toDateString())
            ->latest('scanned_at')
            ->first();
    }
}
