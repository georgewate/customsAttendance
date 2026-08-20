<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'attendance_settings_updated',
            'description' => 'Attendance settings updated.',
            'metadata' => ['new' => ['auto_renew_qr_enabled' => true]],
        ]);

        $this->actingAs($admin)->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('attendance_settings_updated')
            ->assertSee('Attendance settings updated.');
    }

    public function test_manager_cannot_access_audit_log(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->get('/admin/audit-logs')->assertForbidden();
    }

    public function test_guest_is_redirected_from_audit_log(): void
    {
        $this->get('/admin/audit-logs')->assertRedirect('/login');
    }

    public function test_audit_log_filters_by_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        AuditLog::create(['user_id' => $admin->id, 'action' => 'attendance_settings_updated', 'description' => 'Settings saved.']);
        AuditLog::create(['action' => 'qr_codes_rotated', 'description' => 'QR codes rotated.']);

        $this->actingAs($admin)->get('/admin/audit-logs?action=qr_codes_rotated')
            ->assertOk()
            ->assertSee('QR codes rotated.')
            ->assertDontSee('Settings saved.');
    }

    public function test_audit_log_filters_by_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DB::table('audit_logs')->insert([
            ['action' => 'old_entry', 'description' => 'Old entry.', 'created_at' => '2026-08-01 10:00:00'],
            ['action' => 'recent_entry', 'description' => 'Recent entry.', 'created_at' => '2026-08-19 10:00:00'],
        ]);

        $this->actingAs($admin)->get('/admin/audit-logs?from=2026-08-19&to=2026-08-19')
            ->assertOk()
            ->assertSee('Recent entry.')
            ->assertDontSee('Old entry.');
    }

    public function test_audit_log_shows_employee_actor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();

        AuditLog::create([
            'employee_id' => $employee->id,
            'action' => 'attendance_reason_submitted',
            'description' => 'Late arrival reason submitted.',
        ]);

        $this->actingAs($admin)->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('Late arrival reason submitted.');
    }
}
