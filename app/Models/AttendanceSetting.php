<?php

namespace App\Models;

use Database\Factories\AttendanceSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['auto_clock_out_time', 'auto_clock_out_enabled', 'shift_start_time', 'shift_end_time', 'reason_text_max_length', 'auto_renew_qr_enabled', 'updated_by'])]
class AttendanceSetting extends Model
{
    /** @use HasFactory<AttendanceSettingFactory> */
    use HasFactory;

    public const DEFAULT_AUTO_CLOCK_OUT_TIME = '15:00';

    public const DEFAULT_SHIFT_START_TIME = '09:00';

    public const DEFAULT_SHIFT_END_TIME = '17:00';

    public const DEFAULT_REASON_TEXT_MAX_LENGTH = 255;

    protected function casts(): array
    {
        return [
            'auto_clock_out_enabled' => 'boolean',
            'auto_renew_qr_enabled' => 'boolean',
            'reason_text_max_length' => 'integer',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? static::create([
            'auto_clock_out_time' => self::DEFAULT_AUTO_CLOCK_OUT_TIME,
            'auto_clock_out_enabled' => false,
            'shift_start_time' => self::DEFAULT_SHIFT_START_TIME,
            'shift_end_time' => self::DEFAULT_SHIFT_END_TIME,
            'reason_text_max_length' => self::DEFAULT_REASON_TEXT_MAX_LENGTH,
            'auto_renew_qr_enabled' => false,
        ]);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function autoClockOutCarbon(?string $date = null): Carbon
    {
        $date = $date ?? now()->toDateString();

        return Carbon::parse($date.' '.$this->auto_clock_out_time);
    }

    public function shiftStartCarbon(?string $date = null): Carbon
    {
        $date = $date ?? now()->toDateString();

        return Carbon::parse($date.' '.$this->shift_start_time);
    }

    public function shiftEndCarbon(?string $date = null): Carbon
    {
        $date = $date ?? now()->toDateString();

        return Carbon::parse($date.' '.$this->shift_end_time);
    }
}
