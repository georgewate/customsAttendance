<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_admin_can_view_dashboard_with_todays_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee($employee->name);
        $response->assertSee('Checked in today');
        $response->assertSee($location->name);
    }

    public function test_manager_can_view_dashboard(): void
    {
        $location = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $location->id]);

        $this->actingAs($manager)->get('/dashboard')->assertOk();
    }

    public function test_dashboard_stats_are_scoped_to_managers_location(): void
    {
        $ownLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();
        $manager = User::factory()->create(['role' => 'manager', 'location_id' => $ownLocation->id]);

        $ownEmployee = Employee::factory()->create(['home_location_id' => $ownLocation->id]);
        $otherEmployee = Employee::factory()->create(['home_location_id' => $otherLocation->id]);

        AttendanceLog::factory()->create(['employee_id' => $ownEmployee->id, 'location_id' => $ownLocation->id, 'type' => 'time_in']);
        AttendanceLog::factory()->create(['employee_id' => $otherEmployee->id, 'location_id' => $otherLocation->id, 'type' => 'time_in']);

        $response = $this->actingAs($manager)->get('/dashboard');

        $response->assertOk();
        $response->assertSee($ownEmployee->name);
        $response->assertDontSee($otherEmployee->name);
    }

    public function test_dashboard_cards_link_to_relevant_pages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $employee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
        ]);

        $today = now()->toDateString();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee(route('admin.attendance.index', ['filter' => 'on-clock', 'from' => $today, 'to' => $today]));
        $response->assertSee(route('admin.attendance.index', ['from' => $today, 'to' => $today]));
        $response->assertSee(route('admin.attendance.index', ['filter' => 'late']));
        $response->assertSee(route('admin.attendance.index', ['filter' => 'early']));
        $response->assertSee(route('admin.employees.index', ['status' => 'active']));
        $response->assertSee(route('admin.locations.index'));
        $response->assertSee(route('admin.attendance.reports'));
        $response->assertDontSee('open-modal');
    }

    public function test_dashboard_counts_employees_checked_in_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));

        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();

        $onClock = Employee::factory()->create(['home_location_id' => $location->id]);
        $clockedOut = Employee::factory()->create(['home_location_id' => $location->id]);
        $neverClocked = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create(['employee_id' => $onClock->id, 'location_id' => $location->id, 'type' => 'time_in', 'scanned_at' => now()->subHours(3)]);
        AttendanceLog::factory()->create(['employee_id' => $clockedOut->id, 'location_id' => $location->id, 'type' => 'time_in', 'scanned_at' => now()->subHours(5)]);
        AttendanceLog::factory()->create(['employee_id' => $clockedOut->id, 'location_id' => $location->id, 'type' => 'time_out', 'scanned_at' => now()->subHour()]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSeeHtml('<div class="text-2xl font-bold text-neutral-900 dark:text-white">2</div>')
            ->assertSeeHtml('<div class="text-2xl font-bold text-neutral-900 dark:text-white">1</div>');

        Carbon::setTestNow();
    }
}