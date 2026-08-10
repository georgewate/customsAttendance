<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlaywrightPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', ['groups' => $this->connectionPoints()]);
    }

    public function apiDocsPdf()
    {
        $pdf = Pdf::loadView('admin.settings.api-docs-pdf', [
            'groups' => $this->connectionPoints(),
            'generatedAt' => Carbon::now()->format('Y-m-d H:i'),
        ]);

        return $pdf->download('attendance-api-connection-points.pdf');
    }

    public function userGuidePdf(PlaywrightPdf $pdf)
    {
        $file = $pdf->render(
            'admin.settings.user-guide-pdf',
            ['generatedAt' => Carbon::now()->format('F j, Y, g:i A')],
            'User Guide',
        );

        return response()->download($file, 'smart-attendance-user-guide.pdf')
            ->deleteFileAfterSend(true);
    }

    public function technicalDocsPdf(PlaywrightPdf $pdf)
    {
        $file = $pdf->render(
            'admin.settings.technical-docs-pdf',
            ['generatedAt' => Carbon::now()->format('F j, Y, g:i A')],
            'Technical Documentation',
        );

        return response()->download($file, 'smart-attendance-technical-documentation.pdf')
            ->deleteFileAfterSend(true);
    }

    /**
     * Hand-written alongside routes/web.php and routes/admin.php - update
     * this when a route is added, renamed, or removed. This app has no
     * token-based JSON API; every endpoint below is a session/cookie
     * authenticated Blade route, documented here as the integration
     * surface for QR provisioning tools, kiosks, or export automation.
     */
    private function connectionPoints(): array
    {
        return [
            [
                'name' => 'QR Attendance Scan (employee-facing kiosk, no login account required)',
                'note' => 'Scoped by the location\'s QR token in the URL. No session cookie is required to view the page, but time-in/time-out require an active employee scan-session established via the login step below.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/scan/{qr_token}',
                        'description' => 'Shows the location\'s scan screen. Redirects to the employee code/PIN step if no active scan-session exists for this location.',
                        'params' => 'None',
                        'response' => 'HTML page with Time In / Time Out buttons, or the sign-in form.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/scan/{qr_token}/login',
                        'description' => 'Signs an employee into a scan-session for this location.',
                        'params' => 'Body (form): employee_code, pin. Throttled to 20 requests/minute.',
                        'response' => 'Redirect back to the scan screen, signed in.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/scan/{qr_token}/time-in',
                        'description' => 'Records a time-in scan for the signed-in employee at this location.',
                        'params' => 'None (uses the active scan-session). Throttled to 30 requests/minute.',
                        'response' => 'Redirect back to the scan screen with a success/error flash message.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/scan/{qr_token}/time-out',
                        'description' => 'Records a time-out scan for the signed-in employee at this location.',
                        'params' => 'None (uses the active scan-session). Throttled to 30 requests/minute.',
                        'response' => 'Redirect back to the scan screen with a success/error flash message.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/scan/{qr_token}/logout',
                        'description' => 'Ends the employee\'s scan-session for this location (does not affect admin/manager login sessions).',
                        'params' => 'None',
                        'response' => 'Redirect back to the sign-in step.',
                    ],
                ],
            ],
            [
                'name' => 'Admin & Manager Authentication',
                'note' => 'Standard Laravel session/cookie login, not a token API. Log in once in a browser (or an HTTP client that keeps cookies) before calling anything below.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/login',
                        'description' => 'Shows the sign-in page for admins and managers.',
                        'params' => 'None',
                        'response' => 'HTML sign-in form.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/login',
                        'description' => 'Authenticates an admin or manager user and starts a session.',
                        'params' => 'Body (form): email, password. Throttled to 6 attempts/minute.',
                        'response' => 'Redirect to /dashboard with an authenticated session cookie.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/logout',
                        'description' => 'Ends the current session.',
                        'params' => 'None',
                        'response' => 'Redirect to the login page.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/forgot-password',
                        'description' => 'Emails a password reset link to a registered user.',
                        'params' => 'Body (form): email. Throttled to 5 requests/minute.',
                        'response' => 'Redirect back to the forgot-password page.',
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/reset-password',
                        'description' => 'Sets a new password using a valid reset token.',
                        'params' => 'Body (form): token, email, password, password_confirmation. Throttled to 5 requests/minute.',
                        'response' => 'Redirect to the login page.',
                    ],
                ],
            ],
            [
                'name' => 'Dashboard',
                'note' => 'Requires an authenticated session with role admin or manager.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/dashboard',
                        'description' => 'Analytics overview scoped to the signed-in manager\'s location (admins see all locations). KPI cards open detail modals with live lists.',
                        'params' => 'None',
                        'response' => 'HTML dashboard with Chart.js charts and KPI cards.',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'Attendance & Reporting',
                'note' => 'Requires an authenticated session with role admin or manager.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/admin/attendance',
                        'description' => 'Attendance log, filterable by location and date range.',
                        'params' => 'Query: location_id (admin only), from, to (dates).',
                        'response' => 'HTML page listing matching attendance scans.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/attendance/export',
                        'description' => 'Downloads the same filtered attendance log as a CSV file.',
                        'params' => 'Query: location_id (admin only), from, to (dates).',
                        'response' => 'CSV file download.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/attendance/reports',
                        'description' => 'Fortnightly attendance report (10 working days, Mon-Fri).',
                        'params' => 'Query: week_start (date) optional, location_id (admin only) optional.',
                        'response' => 'HTML report page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/attendance/reports/download',
                        'description' => 'Downloads the fortnightly report as an Excel (.xlsx) or PDF file.',
                        'params' => 'Query: format (excel|pdf, default excel), week_start, location_id.',
                        'response' => 'Binary file download (application/vnd.openxmlformats-officedocument.spreadsheetml.sheet or application/pdf).',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'Employee Management',
                'note' => 'Requires an authenticated session with role admin or manager. Managers are restricted to their own location.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/admin/employees',
                        'description' => 'Lists employees.',
                        'params' => 'None',
                        'response' => 'HTML page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/admin/employees',
                        'description' => 'Creates an employee.',
                        'params' => 'Body (form): name, employee_code, pin (min 4, max 8), email, home_location_id, active.',
                        'response' => 'Redirect to the employee list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/admin/employees/{employee}',
                        'description' => 'Updates an employee.',
                        'params' => 'Body (form): same fields as create.',
                        'response' => 'Redirect to the employee list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/admin/employees/{employee}',
                        'description' => 'Removes an employee (attendance history is cascade-deleted).',
                        'params' => 'None',
                        'response' => 'Redirect to the employee list.',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'Locations & QR Codes',
                'note' => 'Requires an authenticated session with role admin.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/admin/locations',
                        'description' => 'Lists scan locations.',
                        'params' => 'None',
                        'response' => 'HTML page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/admin/locations',
                        'description' => 'Creates a scan location.',
                        'params' => 'Body (form): name, address, timezone, active.',
                        'response' => 'Redirect to the location list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/admin/locations/{location}',
                        'description' => 'Updates a scan location.',
                        'params' => 'Body (form): same fields as create.',
                        'response' => 'Redirect to the location list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/admin/locations/{location}',
                        'description' => 'Removes a scan location.',
                        'params' => 'None',
                        'response' => 'Redirect to the location list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/locations/{location}/qr',
                        'description' => 'Shows the printable QR code for a location, encoding its /scan/{qr_token} URL.',
                        'params' => 'None',
                        'response' => 'HTML page with an embedded QR image.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/admin/locations/{location}/regenerate-token',
                        'description' => 'Rotates a location\'s QR token, invalidating its previously printed QR code.',
                        'params' => 'None',
                        'response' => 'Redirect back with the new QR code.',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'User Management',
                'note' => 'Requires an authenticated session with role admin.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/admin/users',
                        'description' => 'Lists admin/manager user accounts.',
                        'params' => 'None',
                        'response' => 'HTML page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/admin/users',
                        'description' => 'Creates an admin/manager user account.',
                        'params' => 'Body (form): name, email, password, role (admin|manager), location_id.',
                        'response' => 'Redirect to the user list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'PUT',
                        'path' => '/admin/users/{user}',
                        'description' => 'Updates a user account.',
                        'params' => 'Body (form): name, email, password (optional), role, location_id.',
                        'response' => 'Redirect to the user list.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/admin/users/{user}',
                        'description' => 'Removes a user account (cannot delete your own account).',
                        'params' => 'None',
                        'response' => 'Redirect to the user list.',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'Settings & Documentation',
                'note' => 'Requires an authenticated session with role admin.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/admin/settings',
                        'description' => 'Settings page with API docs, user guide and technical documentation, plus PDF downloads.',
                        'params' => 'None',
                        'response' => 'HTML page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/settings/api-docs/pdf',
                        'description' => 'Downloads these connection points as a PDF.',
                        'params' => 'None',
                        'response' => 'PDF file download.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/settings/user-guide/pdf',
                        'description' => 'Downloads the end-user guide as a PDF.',
                        'params' => 'None',
                        'response' => 'PDF file download.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/admin/settings/technical-docs/pdf',
                        'description' => 'Downloads the technical documentation as a PDF.',
                        'params' => 'None',
                        'response' => 'PDF file download.',
                        'auth' => true,
                    ],
                ],
            ],
            [
                'name' => 'Profile',
                'note' => 'Requires an authenticated session. Standard Laravel Breeze profile management.',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/profile',
                        'description' => 'Shows the profile edit form.',
                        'params' => 'None',
                        'response' => 'HTML page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'PATCH',
                        'path' => '/profile',
                        'description' => 'Updates the signed-in user\'s name and email (email change requires re-verification).',
                        'params' => 'Body (form): name, email.',
                        'response' => 'Redirect back to the profile page.',
                        'auth' => true,
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/profile',
                        'description' => 'Permanently deletes the signed-in user\'s account.',
                        'params' => 'Body (form): password (must match).',
                        'response' => 'Redirect to the welcome page.',
                        'auth' => true,
                    ],
                ],
            ],
        ];
    }
}
