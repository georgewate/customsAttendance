<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_settings_page_with_qr_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/attendance/settings')
            ->assertOk()
            ->assertSee('QR code auto-renewal');
    }

    public function test_manager_cannot_access_settings_page(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->get('/admin/attendance/settings')->assertForbidden();
    }

    public function test_admin_can_enable_auto_renew_qr_setting(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/attendance/settings', [
            'auto_clock_out_time' => '15:00',
            'shift_start_time' => '09:00',
            'shift_end_time' => '17:00',
            'reason_text_max_length' => 255,
            'auto_renew_qr_enabled' => '1',
        ])->assertRedirect(route('admin.attendance.settings'));

        $this->assertDatabaseHas('attendance_settings', ['auto_renew_qr_enabled' => true]);
    }

    public function test_auto_renew_qr_defaults_to_disabled(): void
    {
        $setting = AttendanceSetting::current();

        $this->assertFalse($setting->auto_renew_qr_enabled);
    }
}
