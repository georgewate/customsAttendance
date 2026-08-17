<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageProbeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_renders_with_realistic_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.attendance.reports'))
            ->assertOk()
            ->assertSee('Fortnightly hours report');
    }
}
