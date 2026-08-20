<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Services\DeduplicateAttendanceLogsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLogDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_keeps_earliest_clock_in_and_latest_clock_out_per_day(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $keepIn = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 08:55:00',
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 09:10:00',
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 09:30:00',
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => '2026-08-19 16:30:00',
        ]);
        $keepOut = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => '2026-08-19 17:05:00',
        ]);

        $deleted = app(DeduplicateAttendanceLogsService::class)->run();

        $this->assertSame(3, $deleted);
        $this->assertDatabaseHas('attendance_logs', ['id' => $keepIn->id]);
        $this->assertDatabaseHas('attendance_logs', ['id' => $keepOut->id]);
        $this->assertSame(2, AttendanceLog::where('employee_id', $employee->id)->count());
    }

    public function test_leaves_unique_logs_and_other_days_untouched(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);
        $other = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 08:55:00',
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => '2026-08-19 17:05:00',
        ]);
        $otherLog = AttendanceLog::factory()->create([
            'employee_id' => $other->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-20 08:55:00',
        ]);

        $deleted = app(DeduplicateAttendanceLogsService::class)->run();

        $this->assertSame(0, $deleted);
        $this->assertDatabaseCount('attendance_logs', 3);
        $this->assertDatabaseHas('attendance_logs', ['id' => $otherLog->id]);
    }

    public function test_writes_audit_entry_when_logs_are_removed(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 08:55:00',
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-19 09:10:00',
        ]);

        app(DeduplicateAttendanceLogsService::class)->run();

        $this->assertDatabaseHas('audit_logs', ['action' => 'attendance_logs_deduplicated']);
    }
}
