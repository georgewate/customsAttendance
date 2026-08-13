<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category', 'name', 'description', 'active', 'created_by', 'updated_by'])]
class AttendanceReason extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceReasonFactory> */
    use HasFactory, SoftDeletes;

    public const CATEGORY_LATE = 'late_arrival';

    public const CATEGORY_EARLY = 'early_clock_out';

    public const CATEGORIES = [
        self::CATEGORY_LATE => 'Late Clock-In',
        self::CATEGORY_EARLY => 'Early Clock-Out',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function lateLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'late_reason_id');
    }

    public function earlyClockOutLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'early_clock_out_reason_id');
    }

    public function inUse(): bool
    {
        return $this->lateLogs()->exists() || $this->earlyClockOutLogs()->exists();
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}
