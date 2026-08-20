<?php

namespace Tests\Feature\Employee;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScanQrTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_without_credentials_is_prompted_to_sign_in(): void
    {
        $location = Location::factory()->create();

        $this->postJson('/employee/scan-qr', ['token' => $location->qr_token])
            ->assertStatus(401)
            ->assertJson(['status' => 'need_auth']);
    }

    public function test_guest_can_clock_in_with_employee_code_and_pin(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 09:30:00'));

        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $response = $this->postJson('/employee/scan-qr', [
            'token' => $location->qr_token,
            'employee_code' => $employee->employee_code,
            'pin' => '1234',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'action' => 'clock_in']);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
        ]);

        $this->assertAuthenticatedAs($employee, 'employee');

        Carbon::setTestNow();
    }

    public function test_guest_with_invalid_pin_is_rejected(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->postJson('/employee/scan-qr', [
            'token' => $location->qr_token,
            'employee_code' => $employee->employee_code,
            'pin' => '9999',
        ])
            ->assertStatus(422)
            ->assertJson(['status' => 'error', 'message' => 'Invalid employee code or PIN.']);

        $this->assertSame(0, AttendanceLog::where('employee_id', $employee->id)->count());
        $this->assertGuest('employee');
    }

    public function test_guest_can_clock_out_when_already_clocked_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 16:00:00'));

        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(8, 55),
        ]);

        $response = $this->postJson('/employee/scan-qr', [
            'token' => $location->qr_token,
            'employee_code' => $employee->employee_code,
            'pin' => '1234',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'action' => 'clock_out']);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_out',
        ]);

        Carbon::setTestNow();
    }

    public function test_guest_already_clocked_out_today_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 18:00:00'));

        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(8, 55),
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(17, 0),
        ]);

        $this->postJson('/employee/scan-qr', [
            'token' => $location->qr_token,
            'employee_code' => $employee->employee_code,
            'pin' => '1234',
        ])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => "You've already clocked out today.",
            ]);

        Carbon::setTestNow();
    }

    public function test_invalid_token_returns_error(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => 'not-a-real-token'])
            ->assertNotFound()
            ->assertJson(['status' => 'error']);
    }

    public function test_inactive_location_token_returns_error(): void
    {
        $location = Location::factory()->inactive()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => $location->qr_token])
            ->assertNotFound()
            ->assertJson(['status' => 'error']);
    }

    public function test_employee_can_clock_in_with_valid_token(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $response = $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => $location->qr_token]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'action' => 'clock_in']);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
        ]);
    }

    public function test_scan_qr_toggles_to_clock_out_when_already_clocked_in(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));

        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->subHours(2),
        ]);

        $response = $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => $location->qr_token]);

        $response->assertOk()
            ->assertJson(['status' => 'ok', 'action' => 'clock_out']);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
        ]);

        Carbon::setTestNow();
    }

    public function test_scan_page_shows_scan_qr_button_and_dashboard_does_not(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Scan QR Code')
            ->assertSee('/employee/scan-qr');

        $this->actingAs($employee, 'employee')
            ->get('/employee/dashboard')
            ->assertOk()
            ->assertDontSee('Scan QR Code');
    }

    public function test_scan_qr_records_late_flag_when_after_shift_start(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 10:00:00'));

        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => $location->qr_token])
            ->assertOk()
            ->assertJson(['status' => 'ok', 'action' => 'clock_in']);

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_in',
            'is_late' => true,
        ]);

        Carbon::setTestNow();
    }

    public function test_scan_qr_rejects_toggle_after_clocking_out_today(): void
    {
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')->postJson('/employee/scan-qr', ['token' => $location->qr_token]);
        $this->actingAs($employee, 'employee')->postJson('/employee/scan-qr', ['token' => $location->qr_token]);

        $this->actingAs($employee, 'employee')
            ->postJson('/employee/scan-qr', ['token' => $location->qr_token])
            ->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => "You've already clocked out today.",
            ]);

        $this->assertSame(2, AttendanceLog::where('employee_id', $employee->id)->count());
    }
}
