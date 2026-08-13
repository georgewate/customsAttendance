<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_shows_documentation_tabs_and_api_docs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('API Docs');
        $response->assertSee('User Guide');
        $response->assertSee('Technical Docs');
        $response->assertSee('/scan/{qr_token}');
        $response->assertSee('/admin/attendance/reports/download');
    }

    public function test_api_docs_pdf_is_downloadable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/settings/api-docs/pdf')
            ->assertOk()
            ->assertDownload('attendance-api-connection-points.pdf');
    }

    public function test_user_guide_pdf_is_downloadable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/settings/user-guide/pdf')
            ->assertOk()
            ->assertDownload('smart-attendance-user-guide.pdf');
    }

    public function test_technical_docs_pdf_is_downloadable(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/settings/technical-docs/pdf')
            ->assertOk()
            ->assertDownload('smart-attendance-technical-documentation.pdf');
    }

    public function test_managers_cannot_access_settings_downloads(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $this->actingAs($manager)->get('/admin/settings')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings/api-docs/pdf')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings/user-guide/pdf')->assertForbidden();
        $this->actingAs($manager)->get('/admin/settings/technical-docs/pdf')->assertForbidden();
    }
}
