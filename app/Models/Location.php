<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['name', 'address', 'timezone', 'qr_token', 'active'])]
class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $location) {
            if (empty($location->qr_token)) {
                $location->qr_token = Str::random(32);
            }
        });
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'home_location_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function scanUrl(): string
    {
        $base = config('app.scan_base_url');

        if ($base) {
            return rtrim($base, '/')."/scan/{$this->qr_token}";
        }

        return url("/scan/{$this->qr_token}");
    }
}
