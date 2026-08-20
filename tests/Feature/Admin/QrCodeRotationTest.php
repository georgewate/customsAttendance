<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceSetting;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeRotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_does_nothing_when_renewal_disabled(): void
    {
        $location = Location::factory()->create();
        $original = $location->qr_token;

        $this->artisan('attendance:rotate-qr-codes')->assertSuccessful();

        $this->assertDatabaseHas('locations', ['id' => $location->id, 'qr_token' => $original]);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_command_rotates_active_location_tokens_when_enabled(): void
    {
        AttendanceSetting::current()->update(['auto_renew_qr_enabled' => true]);

        $active = Location::factory()->create();
        $inactive = Location::factory()->inactive()->create();
        $activeOriginal = $active->qr_token;

        $this->artisan('attendance:rotate-qr-codes')->assertSuccessful();

        $this->assertDatabaseMissing('locations', ['id' => $active->id, 'qr_token' => $activeOriginal]);
        $this->assertDatabaseHas('locations', ['id' => $inactive->id, 'qr_token' => $inactive->qr_token]);
        $this->assertDatabaseCount('audit_logs', 1);
    }
}
