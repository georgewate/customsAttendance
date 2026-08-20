<div class="doc-body">
    <div class="toc">
        <strong>Contents</strong>
        <ol>
            <li>Overview &amp; architecture</li>
            <li>Technology stack</li>
            <li>Directory layout</li>
            <li>Database schema</li>
            <li>Authentication &amp; authorization</li>
            <li>QR scan flow</li>
            <li>Reports &amp; exports</li>
            <li>PDF rendering pipeline</li>
            <li>Configuration &amp; deployment</li>
            <li>Security hardening</li>
            <li>Testing</li>
        </ol>
    </div>

    <h2>1. Overview &amp; architecture</h2>
    <p>
        Smart Attendance is a server-rendered Laravel monolith. There is no separate front-end application and
        no token-based JSON API: every page is a Blade view rendered by a controller, progressively enhanced with
        Alpine.js and Chart.js. All state lives in a single SQLite database. PDF generation uses headless
        Chromium driven by Playwright for documents and DomPDF for the attendance report.
    </p>
    <p>Two independent authentication surfaces exist:</p>
    <ul>
        <li><strong>Back office</strong> &mdash; admins and managers authenticate with email/password via the standard Breeze session login.</li>
        <li><strong>Scan kiosk</strong> &mdash; employees authenticate against a location-scoped, session-based flow using an employee code + hashed PIN.</li>
    </ul>

    <h2>2. Technology stack</h2>
    <table>
        <tr><th>Concern</th><th>Technology</th></tr>
        <tr><td>Framework</td><td>Laravel 13 (PHP 8.5)</td></tr>
        <tr><td>Database</td><td>SQLite (single file, WAL-friendly)</td></tr>
        <tr><td>Front-end</td><td>Blade, Tailwind CSS 3, Alpine.js 3, Chart.js 4, html5-qrcode (camera QR scanning)</td></tr>
        <tr><td>Assets</td><td>Vite 8 (Laravel Vite plugin)</td></tr>
        <tr><td>Reports</td><td>PhpSpreadsheet (.xlsx), barryvdh/laravel-dompdf (report PDF), CSV</td></tr>
        <tr><td>Documentation PDFs</td><td>Playwright + headless Chromium (user guide, technical docs)</td></tr>
        <tr><td>Queues / cache / sessions</td><td>Database driver</td></tr>
    </table>

    <h2>3. Directory layout</h2>
    <table>
        <tr><th>Path</th><th>Purpose</th></tr>
        <tr><td><code>app/Http/Controllers/</code></td><td>Web controllers (<code>ScanController</code>), the employee mobile-scan controller under <code>Employee/ScanQrController</code>, and admin controllers under <code>Admin/</code> (Dashboard, Attendance, AttendanceSettings, AttendanceReason, AuditLog, Employee, Location, Report, User, Settings).</td></tr>
        <tr><td><code>app/Http/Middleware/EnsureUserHasRole.php</code></td><td>Role gate registered as the <code>role</code> alias.</td></tr>
        <tr><td><code>app/Models/</code></td><td>Eloquent models: <code>User</code>, <code>Location</code>, <code>Employee</code>, <code>AttendanceLog</code>, <code>AttendanceSetting</code>, <code>AttendanceReason</code>, <code>AuditLog</code>.</td></tr>
        <tr><td><code>app/Services/</code></td><td><code>PlaywrightPdf</code> (docs PDFs), <code>AutoClockOutService</code>, <code>RotateQrCodesService</code>, <code>DeduplicateAttendanceLogsService</code>.</td></tr>
        <tr><td><code>app/Console/Commands/</code></td><td><code>AutoClockOut</code> (<code>attendance:auto-clockout</code>), <code>RotateQrCodes</code> (<code>attendance:rotate-qr-codes</code>).</td></tr>
        <tr><td><code>routes/web.php</code>, <code>routes/admin.php</code>, <code>routes/auth.php</code></td><td>Route definitions.</td></tr>
        <tr><td><code>resources/js/qr-scanner.js</code></td><td>Lazy-loaded camera overlay that decodes location QR codes and posts the token to the mobile scan endpoint.</td></tr>
        <tr><td><code>resources/views/admin/</code></td><td>Back-office Blade views (dashboard, attendance, reports, settings, audit log, etc.).</td></tr>
        <tr><td><code>resources/views/scan/</code></td><td>Kiosk scan-screen views.</td></tr>
        <tr><td><code>scripts/pdf-html.mjs</code></td><td>Node script that converts an HTML file to a PDF with headless Chromium.</td></tr>
        <tr><td><code>tests/Feature/</code></td><td>Feature tests (PHPUnit).</td></tr>
    </table>

    <h2>4. Database schema</h2>
    <p>All tables are created by the migrations in <code>database/migrations</code>.</p>

    <h3><code>users</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>name</td><td>string</td><td></td></tr>
        <tr><td>email</td><td>string</td><td>unique</td></tr>
        <tr><td>email_verified_at</td><td>timestamp, nullable</td><td></td></tr>
        <tr><td>password</td><td>string</td><td>hashed</td></tr>
        <tr><td>remember_token</td><td>string, nullable</td><td></td></tr>
        <tr><td>role</td><td>enum <code>admin|manager</code></td><td>default <code>manager</code></td></tr>
        <tr><td>location_id</td><td>foreign key, nullable</td><td>scope for managers, <code>nullOnDelete</code></td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
    </table>

    <h3><code>locations</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>name</td><td>string</td><td></td></tr>
        <tr><td>address</td><td>string, nullable</td><td></td></tr>
        <tr><td>timezone</td><td>string</td><td>default <code>UTC</code></td></tr>
        <tr><td>qr_token</td><td>string(40), unique</td><td>random token embedded in the QR URL</td></tr>
        <tr><td>active</td><td>boolean</td><td>default true</td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
    </table>

    <h3><code>employees</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>employee_code</td><td>string(20), unique</td><td></td></tr>
        <tr><td>name</td><td>string</td><td></td></tr>
        <tr><td>email</td><td>string, nullable</td><td></td></tr>
        <tr><td>pin_hash</td><td>string</td><td>bcrypt hash of the 4&ndash;8 digit PIN</td></tr>
        <tr><td>home_location_id</td><td>foreign key, nullable</td><td><code>nullOnDelete</code></td></tr>
        <tr><td>active</td><td>boolean</td><td>default true</td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
    </table>

    <h3><code>attendance_logs</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>employee_id</td><td>foreign key</td><td><code>cascadeOnDelete</code>; indexed with scanned_at</td></tr>
        <tr><td>location_id</td><td>foreign key</td><td><code>cascadeOnDelete</code></td></tr>
        <tr><td>type</td><td>enum <code>time_in|time_out</code></td><td>one clock-in and one clock-out per employee per day are enforced by the controllers</td></tr>
        <tr><td>is_late</td><td>boolean</td><td>default false; clock-in after shift start</td></tr>
        <tr><td>is_early_clock_out</td><td>boolean</td><td>default false; clock-out before shift end</td></tr>
        <tr><td>clock_out_type</td><td>string, nullable</td><td><code>manual</code> or <code>auto</code></td></tr>
        <tr><td>late_reason_id</td><td>foreign key, nullable</td><td>&rarr; <code>attendance_reasons</code> (<code>nullOnDelete</code>)</td></tr>
        <tr><td>late_reason_text</td><td>string, nullable</td><td>custom late-arrival reason</td></tr>
        <tr><td>early_clock_out_reason_id</td><td>foreign key, nullable</td><td>&rarr; <code>attendance_reasons</code> (<code>nullOnDelete</code>)</td></tr>
        <tr><td>early_clock_out_reason_text</td><td>string, nullable</td><td>custom early clock-out reason</td></tr>
        <tr><td>reason_submitted_at</td><td>timestamp, nullable</td><td></td></tr>
        <tr><td>reason_status</td><td>enum <code>pending|approved|rejected</code>, nullable</td><td>reason review state</td></tr>
        <tr><td>reason_reviewed_by</td><td>foreign key, nullable</td><td>&rarr; <code>users</code> (<code>nullOnDelete</code>)</td></tr>
        <tr><td>reason_reviewed_at</td><td>timestamp, nullable</td><td></td></tr>
        <tr><td>reason_review_note</td><td>string, nullable</td><td></td></tr>
        <tr><td>scanned_at</td><td>timestamp</td><td></td></tr>
        <tr><td>auto_clocked_out_at</td><td>timestamp, nullable</td><td>set when the automatic clock-out job closes an open shift</td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
    </table>

    <h3><code>attendance_settings</code></h3>
    <p>A single-row settings table (seeded on creation).</p>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>shift_start_time</td><td>string</td><td>default <code>09:00</code>; late threshold</td></tr>
        <tr><td>shift_end_time</td><td>string</td><td>default <code>17:00</code>; early clock-out threshold</td></tr>
        <tr><td>reason_text_max_length</td><td>unsigned integer</td><td>default 255</td></tr>
        <tr><td>auto_clock_out_time</td><td>string</td><td>default <code>15:00</code></td></tr>
        <tr><td>auto_clock_out_enabled</td><td>boolean</td><td>default false</td></tr>
        <tr><td>auto_renew_qr_enabled</td><td>boolean</td><td>default false; when true, location QR tokens rotate once a day</td></tr>
        <tr><td>updated_by</td><td>foreign key, nullable</td><td>&rarr; <code>users</code> (<code>nullOnDelete</code>)</td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
    </table>

    <h3><code>attendance_reasons</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>category</td><td>enum <code>late_arrival|early_clock_out</code></td><td></td></tr>
        <tr><td>name</td><td>string</td><td></td></tr>
        <tr><td>description</td><td>string, nullable</td><td></td></tr>
        <tr><td>active</td><td>boolean</td><td>default true</td></tr>
        <tr><td>created_by / updated_by</td><td>foreign key, nullable</td><td>&rarr; <code>users</code> (<code>nullOnDelete</code>)</td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
        <tr><td>deleted_at</td><td>timestamp, nullable</td><td>soft deletes</td></tr>
    </table>

    <h3><code>audit_logs</code></h3>
    <table>
        <tr><th>Column</th><th>Type</th><th>Notes</th></tr>
        <tr><td>id</td><td>bigint PK</td><td></td></tr>
        <tr><td>employee_id</td><td>foreign key, nullable</td><td><code>nullOnDelete</code></td></tr>
        <tr><td>user_id</td><td>foreign key, nullable</td><td><code>nullOnDelete</code></td></tr>
        <tr><td>action</td><td>string</td><td>indexed with created_at</td></tr>
        <tr><td>description</td><td>text, nullable</td><td></td></tr>
        <tr><td>metadata</td><td>json, nullable</td><td>before/after values, IDs, etc.</td></tr>
        <tr><td>created_at</td><td>timestamp</td><td></td></tr>
    </table>

    <h3>Framework tables</h3>
    <p><code>cache</code>, <code>jobs</code> (queue), <code>password_reset_tokens</code> and
    <code>sessions</code> are the standard Laravel tables used for the database drivers configured in
    <code>.env</code>.</p>

    <h2>5. Authentication &amp; authorization</h2>
    <h3>Back office (Breeze)</h3>
    <ul>
        <li>Routes in <code>routes/auth.php</code> and <code>routes/admin.php</code>.</li>
        <li>Login, password reset and email verification are throttled (login 6/min, forgot/reset 5/min, verify 6/min).</li>
        <li>Sessions are stored in the database; the session cookie is <code>HttpOnly</code>, <code>SameSite=Lax</code> and <code>Secure</code>.</li>
        <li><code>EnsureUserHasRole</code> middleware (alias <code>role</code>) verifies the <code>users.role</code>
        enum and aborts with 403 for mismatches. Admin controllers use <code>role:admin,manager</code> or <code>role:admin</code> groups.</li>
    </ul>
    <h3>Scan kiosk</h3>
    <ul>
        <li>Public <code>/scan/{qr_token}</code> routes resolve the location by token and reject inactive locations.</li>
        <li>Sign-in validates <code>employee_code</code> + <code>pin</code> against active employees, then stores a
        location-scoped scan session (plain PHP session array, no separate table).</li>
        <li>Time-in/time-out require the active scan session; every attempt is recorded to <code>attendance_logs</code>.</li>
    </ul>

    <h2>6. QR scan flow</h2>
    <h3>Kiosk (desktop / printed poster)</h3>
    <ol>
        <li>The location QR code encodes the URL <code>/scan/{qr_token}</code>.</li>
        <li>Visiting the URL calls <code>ScanController::show</code>; if no scan session exists for that location, the sign-in step is shown.</li>
        <li><code>POST /scan/{qr_token}/login</code> validates the code/PIN, regenerates the session ID and sets the scan session.</li>
        <li><code>POST /scan/{qr_token}/time-in</code> / <code>time-out</code> insert an <code>attendance_logs</code> row with the location and timestamp, then flash a result back to the kiosk.</li>
        <li><code>POST /scan/{qr_token}/logout</code> clears the scan session. Admin sessions are untouched.</li>
    </ol>
    <h3>Mobile camera scan</h3>
    <ol>
        <li>The employee dashboard and the clock-in/out page expose a <strong>Scan QR Code</strong> button.</li>
        <li><code>resources/js/qr-scanner.js</code> opens a camera overlay, lazily loading <code>html5-qrcode</code>.</li>
        <li>The decoded text is parsed for a <code>/scan/{token}</code> URL or a raw 32-character token.</li>
        <li><code>POST /employee/scan-qr</code> (<code>Employee\ScanQrController::toggle</code>, throttled 30/min) looks up the active location and toggles the employee&rsquo;s clock state: clock in if not clocked in today, clock out if clocked in, and rejects the request if the employee already clocked out today.</li>
    </ol>
    <h3>Daily QR rotation</h3>
    <ul>
        <li>When <code>attendance_settings.auto_renew_qr_enabled</code> is true, the scheduled <code>attendance:rotate-qr-codes</code> command (<code>RotateQrCodesService</code>, registered in <code>bootstrap/app.php</code>) regenerates every active location&rsquo;s <code>qr_token</code> once a day, invalidating previously printed posters.</li>
        <li>Each rotation is written to <code>audit_logs</code> (<code>action = qr_codes_rotated</code>) with the affected location IDs.</li>
    </ul>
    <h3>One clock-in / one clock-out per day</h3>
    <p>Both the kiosk (<code>ScanController</code>) and the mobile toggle enforce at most one <code>time_in</code> and one
    <code>time_out</code> per employee per day via <code>Employee::clockedInToday()</code> / <code>Employee::clockedOutToday()</code>.
    The <code>2026_08_20_000002</code> migration (<code>DeduplicateAttendanceLogsService</code>) cleaned up historical duplicates,
    keeping the earliest clock-in and latest clock-out per day, and recorded the result in <code>audit_logs</code>.</p>

    <h2>7. Reports &amp; exports</h2>
    <ul>
        <li><strong>Attendance CSV</strong> &mdash; streamed from the same filtered query used by the log page.</li>
        <li><strong>Fortnightly report</strong> &mdash; starts the period on the Monday of the chosen fortnight (default: current), enumerates the 10 Mon&ndash;Fri working days, and for each employee sums daily hours from the first <code>time_in</code> to the last <code>time_out</code> per day.</li>
        <li><strong>Excel</strong> &mdash; PhpSpreadsheet builds an <code>.xlsx</code> with a styled totals row.</li>
        <li><strong>Report PDF</strong> &mdash; DomPDF renders <code>admin.attendance.reports-pdf</code>.</li>
    </ul>

    <h2>8. PDF rendering pipeline</h2>
    <p>User-facing documents (user guide, technical documentation, API docs) are rendered as A4 PDFs:</p>
    <ul>
        <li>The controller renders a Blade view to an HTML string.</li>
        <li><code>App\Services\PlaywrightPdf</code> writes the HTML to a temp file and invokes
        <code>node scripts/pdf-html.mjs &lt;in&gt; &lt;out&gt; [title]</code> via the Laravel <code>Process</code> facade.</li>
        <li>Playwright launches headless Chromium, loads the HTML with <code>page.setContent()</code>, and calls
        <code>page.pdf()</code> with A4, <code>printBackground</code>, and header/footer templates showing page numbers.</li>
        <li>The generated file is returned as a download with <code>deleteFileAfterSend(true)</code> so no temp files persist.</li>
    </ul>
    <div class="callout">
        Chromium must be installed once per machine with <code>npx playwright install chromium</code>. It is
        downloaded to the user profile (<code>%LOCALAPPDATA%\ms-playwright</code> on Windows), not the project.
    </div>

    <h2>9. Configuration &amp; deployment</h2>
    <ul>
        <li><code>APP_ENV=production</code>, <code>APP_DEBUG=false</code> in production.</li>
        <li><code>SESSION_DRIVER=database</code>, <code>SESSION_LIFETIME=120</code>, <code>SESSION_SECURE_COOKIE=true</code> (requires HTTPS).</li>
        <li><code>QUEUE_CONNECTION=database</code>, <code>CACHE_STORE=database</code>.</li>
        <li>SQLite database file at <code>database/database.sqlite</code>; run <code>php artisan migrate --force</code> after deploy.</li>
        <li>Assets are compiled with <code>npm run build</code> and served by Vite; because the app can be reached
        through a proxy/tunnel, <code>bootstrap/app.php</code> sets <code>trustProxies(at: '*')</code> so HTTPS
        asset URLs are generated correctly behind the tunnel.</li>
    </ul>

    <h2>10. Security hardening</h2>
    <table>
        <tr><th>Control</th><th>Implementation</th></tr>
        <tr><td>Security headers</td><td>Global <code>SecurityHeaders</code> middleware: <code>X-Frame-Options: DENY</code>, <code>X-Content-Type-Options: nosniff</code>, <code>Referrer-Policy</code>, <code>Permissions-Policy: geolocation=(), microphone=(), camera=(self)</code>, and <code>Strict-Transport-Security</code> over HTTPS. The camera policy allows the mobile QR scanner on the app&rsquo;s own origin only.</td></tr>
        <tr><td>Brute-force protection</td><td>Rate limits on login, password reset, verification, scan login, time-in/out, and the mobile scan endpoint (30/min).</td></tr>
        <tr><td>CSRF</td><td>Laravel CSRF verification on every form; tokens present on all scan and admin forms.</td></tr>
        <tr><td>Credentials</td><td>Passwords and PINs stored as bcrypt hashes (<code>pin_hash</code>).</td></tr>
        <tr><td>Session hygiene</td><td>Session ID regenerated on login; <code>HttpOnly</code> + <code>Secure</code> cookie flags.</td></tr>
        <tr><td>Mass assignment</td><td>All models whitelist fields via <code>#[Fillable]</code>.</td></tr>
        <tr><td>Output escaping</td><td>Blade escapes all output; no raw <code>@verbatim{!! !!}@endverbatim</code> echo sites.</td></tr>
        <tr><td>Audit coverage</td><td><code>audit_logs</code> records attendance reasons, edits, approvals/rejections, QR rotations, and one-off maintenance jobs; reviewable in the admin <strong>Audit Log</strong> page.</td></tr>
        <tr><td>Dependency audits</td><td><code>composer audit</code> and <code>npm audit</code> report no known vulnerabilities.</td></tr>
    </table>

    <h2>11. Testing</h2>
    <ul>
        <li>PHPUnit feature tests in <code>tests/Feature</code> run against in-memory SQLite.</li>
        <li>Coverage includes admin/manager access control, the scan flow, dashboards, reports, attendance reasons/settings, the one-clock-in/one-clock-out rule, the mobile scan endpoint, the audit-log viewer, deduplication, settings docs, and security headers.</li>
        <li>Run with <code>php artisan test</code>; code style with <code>vendor/bin/pint</code>.</li>
    </ul>
</div>
