<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLogEditTest extends TestCase
{
    use RefreshDatabase;

    private function timeInLog(): AttendanceLog
    {
        $employee = Employee::factory()->create();

        return AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
            'is_late' => true,
        ]);
    }

    public function test_admin_can_update_clock_in_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->timeInLog();

        $this->actingAs($admin)
            ->patch(route('admin.attendance.update', $log), [
                'scanned_at' => now()->format('Y-m-d').' 08:00:00',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'is_late' => false,
        ]);

        $this->assertSame(
            now()->format('Y-m-d').' 08:00:00',
            $log->fresh()->scanned_at->format('Y-m-d H:i:s')
        );
    }

    public function test_admin_can_add_a_reason_to_a_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->timeInLog();
        $reason = AttendanceReason::query()
            ->where('category', 'late_arrival')
            ->active()
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.attendance.update', $log), [
                'scanned_at' => $log->scanned_at->format('Y-m-d H:i:s'),
                'late_reason_id' => $reason->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'late_reason_id' => $reason->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
            'reason_reviewed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_edit_clock_out_time_and_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        $log = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(16, 30),
            'is_early_clock_out' => true,
        ]);
        $reason = AttendanceReason::query()
            ->where('category', 'early_clock_out')
            ->active()
            ->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.attendance.update', $log), [
                'scanned_at' => now()->format('Y-m-d').' 15:00:00',
                'early_clock_out_reason_id' => $reason->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'is_early_clock_out' => true,
            'early_clock_out_reason_id' => $reason->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
        ]);
    }

    public function test_manager_cannot_edit_attendance_records(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);
        $log = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now(),
        ]);

        $this->actingAs($manager)
            ->patch(route('admin.attendance.update', $log), [
                'scanned_at' => now()->format('Y-m-d').' 08:00:00',
            ])
            ->assertForbidden();

        $this->assertSame(
            $log->scanned_at->format('Y-m-d H:i:s'),
            $log->fresh()->scanned_at->format('Y-m-d H:i:s')
        );
    }

    public function test_guest_cannot_edit_attendance_records(): void
    {
        $log = $this->timeInLog();

        $this->patch(route('admin.attendance.update', $log), [
            'scanned_at' => now()->format('Y-m-d').' 08:00:00',
        ])->assertRedirect('/login');
    }

    public function test_updating_a_record_creates_an_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->timeInLog();

        $this->actingAs($admin)
            ->patch(route('admin.attendance.update', $log), [
                'scanned_at' => now()->format('Y-m-d').' 08:00:00',
            ]);

        $entry = AuditLog::where('action', 'attendance_log_updated')->first();
        $this->assertNotNull($entry);
        $this->assertSame((int) $admin->id, (int) $entry->user_id);
        $this->assertSame((int) $log->id, (int) $entry->metadata['attendance_log_id']);
        $this->assertSame($log->scanned_at->format('Y-m-d H:i:s'), $entry->metadata['old_scanned_at']);
    }

    public function test_attendance_page_shows_edit_button_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->timeInLog();

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertSee('Edit');
        $response->assertSee('Save changes');
    }

    public function test_attendance_page_hides_edit_button_for_manager(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now(),
        ]);

        $response = $this->actingAs($manager)->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertDontSee('Save changes');
    }

    public function test_admin_can_edit_both_time_in_and_time_out_together(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        $timeIn = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
        ]);
        $timeOut = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(17, 30),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.attendance.update-day'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
                'time_in' => now()->format('Y-m-d').' 08:00:00',
                'time_out' => now()->format('Y-m-d').' 16:00:00',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('08:00:00', $timeIn->fresh()->scanned_at->format('H:i:s'));
        $this->assertSame('16:00:00', $timeOut->fresh()->scanned_at->format('H:i:s'));
    }

    public function test_admin_can_add_reasons_while_editing_both_times(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        $timeIn = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
        ]);
        $lateReason = AttendanceReason::query()
            ->where('category', 'late_arrival')
            ->active()
            ->firstOrFail();
        $earlyReason = AttendanceReason::query()
            ->where('category', 'early_clock_out')
            ->active()
            ->firstOrFail();
        $timeOut = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(17, 30),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.attendance.update-day'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
                'time_in' => now()->format('Y-m-d').' 09:30:00',
                'time_in_late_reason_id' => $lateReason->id,
                'time_out' => now()->format('Y-m-d').' 16:00:00',
                'time_out_early_reason_id' => $earlyReason->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $timeIn->id,
            'late_reason_id' => $lateReason->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
        ]);
        $this->assertDatabaseHas('attendance_logs', [
            'id' => $timeOut->id,
            'early_clock_out_reason_id' => $earlyReason->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
        ]);
    }

    public function test_admin_can_create_a_missing_clock_out_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        $timeIn = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.attendance.update-day'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
                'time_in' => now()->format('Y-m-d').' 09:30:00',
                'time_out' => now()->format('Y-m-d').' 17:00:00',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_out',
            'scanned_at' => now()->format('Y-m-d').' 17:00:00',
        ]);
        $this->assertSame('09:30:00', $timeIn->fresh()->scanned_at->format('H:i:s'));
    }

    public function test_manager_cannot_edit_day_records(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);
        $timeIn = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
        ]);

        $this->actingAs($manager)
            ->post(route('admin.attendance.update-day'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
                'time_in' => now()->format('Y-m-d').' 08:00:00',
            ])
            ->assertForbidden();

        $this->assertSame('09:30:00', $timeIn->fresh()->scanned_at->format('H:i:s'));
    }

    public function test_guest_cannot_edit_day_records(): void
    {
        $employee = Employee::factory()->create();

        $this->post(route('admin.attendance.update-day'), [
            'employee_id' => $employee->id,
            'date' => now()->format('Y-m-d'),
            'time_in' => now()->format('Y-m-d').' 08:00:00',
        ])->assertRedirect('/login');
    }

    public function test_updating_day_requires_at_least_one_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.attendance.update-day'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('time_in');
    }

    public function test_attendance_page_groups_logs_by_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 30),
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(17, 30),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertSee('9:30 AM', false);
        $response->assertSee('5:30 PM', false);
        $response->assertSee(now()->format('M j, Y'));
    }
}
