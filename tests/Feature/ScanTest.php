<?php

namespace Tests\Feature;

use App\Models\AttendanceLog;
use App\Models\AttendanceReason;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ScanTest extends TestCase
{
    use RefreshDatabase;

    private function location(): Location
    {
        return Location::factory()->create();
    }

    public function test_inactive_location_scan_page_returns_404(): void
    {
        $location = Location::factory()->inactive()->create();

        $this->get("/scan/{$location->qr_token}")->assertNotFound();
    }

    public function test_scan_url_uses_configured_public_base_when_set(): void
    {
        config(['app.scan_base_url' => 'https://attendance.example.com']);

        $location = $this->location();

        $this->assertSame(
            "https://attendance.example.com/scan/{$location->qr_token}",
            $location->scanUrl()
        );
    }

    public function test_scan_url_falls_back_to_request_host_without_override(): void
    {
        config(['app.scan_base_url' => null]);

        $location = $this->location();

        $this->assertSame(
            "http://localhost/scan/{$location->qr_token}",
            $location->scanUrl()
        );
    }

    public function test_scan_page_shows_login_form_for_guest(): void
    {
        $location = $this->location();

        $this->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Employee code');
    }

    public function test_employee_can_login_with_valid_credentials(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id, 'pin_hash' => bcrypt('4321')]);

        $this->post("/scan/{$location->qr_token}/login", [
            'employee_code' => $employee->employee_code,
            'pin' => '4321',
        ])->assertRedirect("/scan/{$location->qr_token}");

        $this->assertAuthenticated('employee');
    }

    public function test_employee_cannot_login_with_invalid_pin(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->post("/scan/{$location->qr_token}/login", [
            'employee_code' => $employee->employee_code,
            'pin' => '9999',
        ])->assertSessionHasErrors('employee_code');

        $this->assertGuest('employee');
    }

    public function test_inactive_employee_cannot_login(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->inactive()->create(['home_location_id' => $location->id]);

        $this->post("/scan/{$location->qr_token}/login", [
            'employee_code' => $employee->employee_code,
            'pin' => '1234',
        ])->assertSessionHasErrors('employee_code');

        $this->assertGuest('employee');
    }

    public function test_time_in_requires_an_employee_session(): void
    {
        $location = $this->location();

        $this->post("/scan/{$location->qr_token}/time-in")->assertForbidden();
    }

    public function test_time_in_records_an_attendance_log(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 9, 30, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", ['late_other_reason' => 'Traffic'], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'is_late' => true,
            'late_reason_text' => 'Traffic',
        ]);
    }

    public function test_time_in_without_reason_is_allowed_when_late(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 9, 30, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", [], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_in',
            'is_late' => true,
            'late_reason_id' => null,
            'late_reason_text' => null,
            'reason_submitted_at' => null,
        ]);
    }

    public function test_submitted_reason_is_recorded_as_pending(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 9, 30, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);
        $reason = AttendanceReason::query()
            ->where('category', 'late_arrival')
            ->active()
            ->firstOrFail();

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", ['late_reason_id' => $reason->id], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_in',
            'late_reason_id' => $reason->id,
            'reason_status' => 'pending',
        ]);
    }

    public function test_double_time_in_is_rejected(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now(),
        ]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", [], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_logs', 1);
    }

    public function test_future_dated_logs_do_not_break_the_time_in_button(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 9, 30, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => Carbon::create(2026, 8, 19, 17, 0, 0),
        ]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", [], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('success');

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-in", [], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('error');

        $this->assertSame(2, AttendanceLog::where('employee_id', $employee->id)->count());

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Clocked in since');
    }

    public function test_time_out_without_time_in_is_rejected(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-out", [], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('error');

        $this->assertDatabaseCount('attendance_logs', 0);
    }

    public function test_time_out_after_time_in_records_a_log(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')->post("/scan/{$location->qr_token}/time-in", ['late_other_reason' => 'Traffic'], ['Referer' => url("/scan/{$location->qr_token}")]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/time-out", ['early_other_reason' => 'Appointment'], ['Referer' => url("/scan/{$location->qr_token}")])
            ->assertRedirect("/scan/{$location->qr_token}")
            ->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'employee_id' => $employee->id,
            'type' => 'time_out',
        ]);
    }

    public function test_scan_show_page_shows_employee_info_and_shift_schedule(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id, 'department' => 'Engineering']);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('Shift start')
            ->assertSee('Shift end')
            ->assertSee('Home location')
            ->assertSee('Time In')
            ->assertSee('Not clocked in today');
    }

    public function test_scan_show_page_shows_empty_history_for_new_employee(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Recent attendance')
            ->assertSee('No attendance recorded in the last 7 days.');
    }

    public function test_scan_show_page_shows_employee_photo_next_to_name(): void
    {
        Storage::fake('public');

        $location = $this->location();
        $employee = Employee::factory()->create([
            'home_location_id' => $location->id,
            'photo' => 'employees/photo.jpg',
        ]);

        Storage::disk('public')->put('employees/photo.jpg', 'image');

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee(asset('storage/employees/photo.jpg'), false);
    }

    public function test_scan_show_page_shows_recent_history_when_logs_exist(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->subDays(2)->setTime(9, 5),
            'is_late' => true,
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => now()->subDays(2)->setTime(17, 2),
        ]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Recent attendance')
            ->assertSee('Late');
    }

    public function test_scan_show_page_shows_todays_activity_for_clocked_in_employee(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 10, 0, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 5),
        ]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Clocked in since')
            ->assertSee("Today's activity", false)
            ->assertSee('Time in');
    }

    public function test_scan_show_page_shows_reason_locked_after_clock_in(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 10, 0, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 5),
        ]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Reason locked')
            ->assertSee('A reason can no longer be added after clocking in.');
    }

    public function test_scan_show_page_shows_submitted_reason_when_locked(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 10, 0, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(9, 5),
            'is_late' => true,
            'late_reason_text' => 'Traffic',
            'reason_submitted_at' => now(),
            'reason_status' => AttendanceLog::REASON_PENDING,
        ]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Reason locked')
            ->assertSee('Traffic')
            ->assertSee('Pending');
    }

    public function test_scan_show_page_shows_reason_locked_after_clock_out(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 19, 18, 0, 0));

        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->setTime(8, 55),
        ]);

        AttendanceLog::create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => now()->setTime(17, 5),
        ]);

        $this->actingAs($employee, 'employee')
            ->get("/scan/{$location->qr_token}")
            ->assertOk()
            ->assertSee('Reason locked')
            ->assertSee('A reason can no longer be added after clocking out.');
    }

    public function test_employee_can_logout_of_scan_session(): void
    {
        $location = $this->location();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($employee, 'employee')
            ->post("/scan/{$location->qr_token}/logout")
            ->assertRedirect("/scan/{$location->qr_token}");

        $this->assertGuest('employee');
    }
}
