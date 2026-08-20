<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReasonApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function pendingLog(): AttendanceLog
    {
        $employee = Employee::factory()->create(['name' => 'Alex Johnson']);

        return AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
            'is_late' => true,
            'late_reason_text' => 'Traffic',
            'reason_submitted_at' => now(),
            'reason_status' => AttendanceLog::REASON_PENDING,
        ]);
    }

    public function test_admin_can_approve_a_pending_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();

        $this->actingAs($admin)
            ->post(route('admin.attendance.approve-reason', $log))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
            'reason_reviewed_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'employee_id' => $log->employee_id,
            'action' => 'attendance_reason_approved',
        ]);
    }

    public function test_admin_can_reject_a_pending_reason_with_note(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();

        $this->actingAs($admin)
            ->post(route('admin.attendance.reject-reason', $log), [
                'reason_review_note' => 'No evidence provided.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'reason_status' => AttendanceLog::REASON_REJECTED,
            'reason_reviewed_by' => $admin->id,
            'reason_review_note' => 'No evidence provided.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'employee_id' => $log->employee_id,
            'action' => 'attendance_reason_rejected',
        ]);
    }

    public function test_approving_an_already_reviewed_reason_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();
        $log->update(['reason_status' => AttendanceLog::REASON_APPROVED]);

        $this->actingAs($admin)
            ->post(route('admin.attendance.approve-reason', $log))
            ->assertStatus(409);

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
        ]);
    }

    public function test_manager_can_approve_a_pending_reason(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $log = AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'is_late' => true,
            'late_reason_text' => 'Traffic',
            'reason_status' => AttendanceLog::REASON_PENDING,
        ]);

        $this->actingAs($manager)
            ->post(route('admin.attendance.approve-reason', $log))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'id' => $log->id,
            'reason_status' => AttendanceLog::REASON_APPROVED,
            'reason_reviewed_by' => $manager->id,
        ]);
    }

    public function test_guests_cannot_approve_or_reject_reasons(): void
    {
        $log = $this->pendingLog();

        $this->post(route('admin.attendance.approve-reason', $log))->assertRedirect('/login');
        $this->post(route('admin.attendance.reject-reason', $log))->assertRedirect('/login');
    }

    public function test_attendance_page_shows_pending_reason_and_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertSee('Traffic');
        $response->assertSee('Pending');
        $response->assertSee('Approve');
        $response->assertSee('Reject');
        $response->assertSee(route('admin.attendance.approve-reason', $log), false);
        $response->assertSee(route('admin.attendance.reject-reason', $log), false);
        $response->assertSee('Approve '.$log->employee->name.'\u0027s late arrival reason? \u0022Traffic\u0022', false);
        $response->assertSee('Reject '.$log->employee->name.'\u0027s late arrival reason? \u0022Traffic\u0022', false);
    }

    public function test_approved_reason_shows_approved_badge_without_actions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();
        $log->update([
            'reason_status' => AttendanceLog::REASON_APPROVED,
            'reason_reviewed_by' => $admin->id,
            'reason_reviewed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertSee('Traffic');
        $response->assertSee('Approved');
        $response->assertDontSee(route('admin.attendance.approve-reason', $log), false);
        $response->assertDontSee(route('admin.attendance.reject-reason', $log), false);
    }

    public function test_audit_log_is_created_when_reason_is_approved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $log = $this->pendingLog();

        $this->actingAs($admin)->post(route('admin.attendance.approve-reason', $log));

        $entry = AuditLog::where('action', 'attendance_reason_approved')->first();
        $this->assertNotNull($entry);
        $this->assertSame((int) $log->id, (int) $entry->metadata['attendance_log_id']);
        $this->assertSame('Traffic', $entry->metadata['reason']);
    }
}
