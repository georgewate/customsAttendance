<?php

namespace Tests\Feature\Admin;

use App\Models\Employee;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $location = Location::factory()->create();
        $employee = Employee::factory()->create(['home_location_id' => $location->id]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.reports'))
            ->assertOk()
            ->assertSee('Reports');
    }
}
