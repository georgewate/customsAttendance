<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_returns_a_successful_response(): void
    {
        $response = $this->get(route('contact'));

        $response->assertStatus(200);
    }

    public function test_contact_page_shows_support_team_heading_and_description(): void
    {
        $response = $this->get(route('contact'));

        $response->assertSee('Contact ASYCUDA Support Team');
        $response->assertSee('If you experience any issues or require assistance with the ASYCUDA system, please contact the support team using the details below.');
    }

    public function test_contact_page_shows_email_and_phone_links(): void
    {
        $response = $this->get(route('contact'));

        $response->assertSee('mailto:awhelpdesk@customs.gov.sb');
        $response->assertSee('awhelpdesk@customs.gov.sb');
        $response->assertSee('tel:24377');
        $response->assertSee('24377');
        $response->assertSee('tel:20038');
        $response->assertSee('20038');
    }

    public function test_contact_page_shows_support_notice(): void
    {
        $response = $this->get(route('contact'));

        $response->assertSee('Support Notice');
        $response->assertSee('For any ASYCUDA system issues, account access problems, technical errors, or support requests, please contact the ASYCUDA Team using the contact details above.');
    }
}
