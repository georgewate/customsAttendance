<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['employee_id', 'location_id', 'type', 'scanned_at', 'clock_out_type', 'auto_clocked_out_at', 'is_late', 'is_early_clock_out', 'late_reason_id', 'late_reason_text', 'early_clock_out_reason_id', 'early_clock_out_reason_text', 'reason_submitted_at', 'reason_status', 'reason_reviewed_by', 'reason_reviewed_at', 'reason_review_note'])]
class AttendanceLog extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'auto_clocked_out_at' => 'datetime',
            'reason_submitted_at' => 'datetime',
            'reason_reviewed_at' => 'datetime',
            'is_late' => 'boolean',
            'is_early_clock_out' => 'boolean',
        ];
    }

    public const REASON_PENDING = 'pending';

    public const REASON_APPROVED = 'approved';

    public const REASON_REJECTED = 'rejected';

    public const REASON_STATUSES = [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    public function hasReason(): bool
    {
        return $this->late_reason_id !== null
            || $this->late_reason_text !== null
            || $this->early_clock_out_reason_id !== null
            || $this->early_clock_out_reason_text !== null;
    }

    public function reasonStatusLabel(): ?string
    {
        return $this->reason_status ? (self::REASON_STATUSES[$this->reason_status] ?? ucfirst($this->reason_status)) : null;
    }

    public function reasonIsPending(): bool
    {
        return $this->hasReason() && $this->reason_status === self::REASON_PENDING;
    }

    public function reasonSummary(): string
    {
        $parts = [];

        if ($this->late_reason_id || $this->late_reason_text) {
            $parts[] = $this->lateReason?->name ?? $this->late_reason_text;
        }

        if ($this->early_clock_out_reason_id || $this->early_clock_out_reason_text) {
            $parts[] = $this->earlyClockOutReason?->name ?? $this->early_clock_out_reason_text;
        }

        return implode('; ', array_filter($parts));
    }

    public function wasAutoClockedOut(): bool
    {
        return $this->type === 'time_out' && $this->clock_out_type === 'automatic';
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function lateReason()
    {
        return $this->belongsTo(AttendanceReason::class, 'late_reason_id');
    }

    public function earlyClockOutReason()
    {
        return $this->belongsTo(AttendanceReason::class, 'early_clock_out_reason_id');
    }
}
