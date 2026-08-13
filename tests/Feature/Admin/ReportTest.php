<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_reports_page(): void
    {
        $this->get('/admin/attendance/reports')->assertRedirect('/login');
    }

    public function test_admin_can_access_reports_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/attendance/reports')->assertOk();
    }

    public function test_manager_can_access_reports_page(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);

        $this->actingAs($manager)->get('/admin/attendance/reports')->assertOk();
    }

    public function test_report_tallies_hours_for_each_working_day(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $monday = Carbon::parse('2026-08-03'); // Monday
        AttendanceLog::factory()->create(['employee_id' => $employee->id, 'location_id' => $location->id, 'type' => 'time_in', 'scanned_at' => $monday->copy()->setTime(9, 0)]);
        AttendanceLog::factory()->create(['employee_id' => $employee->id, 'location_id' => $location->id, 'type' => 'time_out', 'scanned_at' => $monday->copy()->setTime(17, 0)]);

        $tuesday = Carbon::parse('2026-08-04');
        AttendanceLog::factory()->create(['employee_id' => $employee->id, 'location_id' => $location->id, 'type' => 'time_in', 'scanned_at' => $tuesday->copy()->setTime(9, 0)]);
        AttendanceLog::factory()->create(['employee_id' => $employee->id, 'location_id' => $location->id, 'type' => 'time_out', 'scanned_at' => $tuesday->copy()->setTime(13, 0)]);

        $response = $this->actingAs($admin)->get('/admin/attendance/reports?start=2026-08-03');

        $response->assertOk();
        $response->assertSee('8.00h'); // Monday
        $response->assertSee('4.00h'); // Tuesday
        $response->assertSee('12.00h'); // Fortnight total
        $response->assertSee('2 / 10'); // days worked
    }

    public function test_report_is_scoped_to_managers_location(): void
    {
        $ownLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $ownLocation->id]);

        $ownEmployee = Employee::factory()->create(['home_location_id' => $ownLocation->id]);
        $otherEmployee = Employee::factory()->create(['home_location_id' => $otherLocation->id]);

        $day = Carbon::parse('2026-08-03')->setTime(9, 0);
        AttendanceLog::factory()->create(['employee_id' => $ownEmployee->id, 'location_id' => $ownLocation->id, 'type' => 'time_in', 'scanned_at' => $day]);
        AttendanceLog::factory()->create(['employee_id' => $otherEmployee->id, 'location_id' => $otherLocation->id, 'type' => 'time_in', 'scanned_at' => $day]);

        $response = $this->actingAs($manager)->get('/admin/attendance/reports?start=2026-08-03');

        $response->assertOk();
        $response->assertSee($ownEmployee->name);
        $response->assertDontSee($otherEmployee->name);
    }

    public function test_report_downloads_as_excel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/attendance/reports/download?start=2026-08-03&format=excel')
            ->assertOk()
            ->assertDownload('attendance-report-2026-08-03.xlsx')
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_report_downloads_as_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/attendance/reports/download?start=2026-08-03&format=pdf')
            ->assertOk()
            ->assertDownload('attendance-report-2026-08-03.pdf')
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
