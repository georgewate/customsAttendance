<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceFilterTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_filter_late_shows_only_late_arrivals(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));

        $location = Location::factory()->create();
        $lateEmployee = Employee::factory()->create(['home_location_id' => $location->id]);
        $onTimeEmployee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $lateEmployee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-20 10:00:00',
            'is_late' => true,
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $onTimeEmployee->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => '2026-08-20 08:30:00',
            'is_late' => false,
        ]);

        $this->actingAs($this->admin())->get('/admin/attendance?filter=late')
            ->assertOk()
            ->assertSee($lateEmployee->name)
            ->assertDontSee($onTimeEmployee->name);

        Carbon::setTestNow();
    }

    public function test_filter_early_shows_only_early_clock_outs(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));

        $location = Location::factory()->create();
        $earlyEmployee = Employee::factory()->create(['home_location_id' => $location->id]);
        $onTimeEmployee = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $earlyEmployee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => '2026-08-20 16:00:00',
            'is_early_clock_out' => true,
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $onTimeEmployee->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => '2026-08-20 17:30:00',
            'is_early_clock_out' => false,
        ]);

        $this->actingAs($this->admin())->get('/admin/attendance?filter=early')
            ->assertOk()
            ->assertSee($earlyEmployee->name)
            ->assertDontSee($onTimeEmployee->name);

        Carbon::setTestNow();
    }

    public function test_filter_on_clock_shows_only_employees_still_clocked_in(): void
    {
        $location = Location::factory()->create();
        $onClock = Employee::factory()->create(['home_location_id' => $location->id]);
        $clockedOut = Employee::factory()->create(['home_location_id' => $location->id]);

        AttendanceLog::factory()->create([
            'employee_id' => $onClock->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->subHours(2),
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $clockedOut->id,
            'location_id' => $location->id,
            'type' => 'time_in',
            'scanned_at' => now()->subHours(4),
        ]);
        AttendanceLog::factory()->create([
            'employee_id' => $clockedOut->id,
            'location_id' => $location->id,
            'type' => 'time_out',
            'scanned_at' => now()->subHour(),
        ]);

        $today = now()->toDateString();

        $this->actingAs($this->admin())
            ->get("/admin/attendance?filter=on-clock&from={$today}&to={$today}")
            ->assertOk()
            ->assertSee($onClock->name)
            ->assertDontSee($clockedOut->name);
    }

    public function test_employee_index_filters_to_active_employees(): void
    {
        $location = Location::factory()->create();
        $active = Employee::factory()->create(['home_location_id' => $location->id, 'active' => true]);
        $inactive = Employee::factory()->create(['home_location_id' => $location->id, 'active' => false]);

        $this->actingAs($this->admin())->get('/admin/employees?status=active')
            ->assertOk()
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }
}
