<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_pages(): void
    {
        $this->get('/admin/attendance')->assertRedirect('/login');
        $this->get('/admin/employees')->assertRedirect('/login');
        $this->get('/admin/locations')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_managers_can_access_attendance_and_employees(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);

        $this->actingAs($manager)->get('/admin/attendance')->assertOk();
        $this->actingAs($manager)->get('/admin/employees')->assertOk();
    }

    public function test_managers_cannot_access_admin_only_pages(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);

        $this->actingAs($manager)->get('/admin/locations')->assertForbidden();
        $this->actingAs($manager)->get('/admin/users')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings')->assertForbidden();
    }

    public function test_admins_can_access_all_admin_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/attendance')->assertOk();
        $this->actingAs($admin)->get('/admin/employees')->assertOk();
        $this->actingAs($admin)->get('/admin/locations')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();
    }

    public function test_admin_can_create_a_location_with_auto_generated_qr_token(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/locations', [
            'name' => 'Branch Office',
            'address' => '2 Main Street',
            'timezone' => 'Asia/Manila',
            'active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('locations', ['name' => 'Branch Office']);

        $location = Location::where('name', 'Branch Office')->first();
        $this->assertNotEmpty($location->qr_token);
    }

    public function test_manager_cannot_create_an_employee_in_another_location(): void
    {
        $ownLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $ownLocation->id]);

        $this->actingAs($manager)->post('/admin/employees', [
            'employee_code' => 'EMP900',
            'name' => 'Intruder',
            'pin' => '1234',
            'home_location_id' => $otherLocation->id,
        ])->assertForbidden();

        $this->assertDatabaseMissing('employees', ['employee_code' => 'EMP900']);
    }

    public function test_admin_can_create_an_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();

        $this->actingAs($admin)->post('/admin/employees', [
            'employee_code' => 'EMP001',
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'pin' => '1234',
            'home_location_id' => $location->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('employees', ['employee_code' => 'EMP001', 'name' => 'Juan Dela Cruz']);
    }

    public function test_attendance_export_returns_csv_of_matching_logs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = Employee::factory()->create();
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $employee->home_location_id,
            'type' => 'time_in',
        ]);

        $this->actingAs($admin)
            ->get('/admin/attendance/export')
            ->assertOk()
            ->assertDownload('attendance.csv')
            ->assertHeader('Content-Disposition', 'attachment; filename=attendance.csv');
    }

    public function test_attendance_log_is_scoped_to_managers_location(): void
    {
        $ownLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $ownLocation->id]);

        $ownEmployee = Employee::factory()->create(['home_location_id' => $ownLocation->id]);
        $otherEmployee = Employee::factory()->create(['home_location_id' => $otherLocation->id]);

        AttendanceLog::factory()->create(['employee_id' => $ownEmployee->id, 'location_id' => $ownLocation->id]);
        AttendanceLog::factory()->create(['employee_id' => $otherEmployee->id, 'location_id' => $otherLocation->id]);

        $response = $this->actingAs($manager)->get('/admin/attendance');

        $response->assertOk();
        $response->assertSee($ownEmployee->name);
        $response->assertDontSee($otherEmployee->name);
    }
}
