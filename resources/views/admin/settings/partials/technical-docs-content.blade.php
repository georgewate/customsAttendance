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
        <tr><td>Front-end</td><td>Blade, Tailwind CSS 3, Alpine.js 3, Chart.js 4</td></tr>
        <tr><td>Assets</td><td>Vite 8 (Laravel Vite plugin)</td></tr>
        <tr><td>Reports</td><td>PhpSpreadsheet (.xlsx), barryvdh/laravel-dompdf (report PDF), CSV</td></tr>
        <tr><td>Documentation PDFs</td><td>Playwright + headless Chromium (user guide, technical docs)</td></tr>
        <tr><td>Queues / cache / sessions</td><td>Database driver</td></tr>
    </table>

    <h2>3. Directory layout</h2>
    <table>
        <tr><th>Path</th><th>Purpose</th></tr>
        <tr><td><code>app/Http/Controllers/</code></td><td>Web controllers (<code>ScanController</code>) and admin controllers under <code>Admin/</code> (Dashboard, Attendance, Employee, Location, Report, User, Settings).</td></tr>
        <tr><td><code>app/Http/Middleware/EnsureUserHasRole.php</code></td><td>Role gate registered as the <code>role</code> alias.</td></tr>
        <tr><td><code>app/Models/</code></td><td>Eloquent models: <code>User</code>, <code>Location</code>, <code>Employee</code>, <code>AttendanceLog</code>.</td></tr>
        <tr><td><code>app/Services/PlaywrightPdf.php</code></td><td>Renders a Blade view to PDF via the Playwright Node script.</td></tr>
        <tr><td><code>routes/web.php</code>, <code>routes/admin.php</code>, <code>routes/auth.php</code></td><td>Route definitions.</td></tr>
        <tr><td><code>resources/views/admin/</code></td><td>Back-office Blade views (dashboard, attendance, reports, settings, etc.).</td></tr>
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
        <tr><td>type</td><td>enum <code>time_in|time_out</code></td><td></td></tr>
        <tr><td>scanned_at</td><td>timestamp</td><td></td></tr>
        <tr><td>timestamps</td><td></td><td></td></tr>
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
    <ol>
        <li>The location QR code encodes the URL <code>/scan/{qr_token}</code>.</li>
        <li>Visiting the URL calls <code>ScanController::show</code>; if no scan session exists for that location, the sign-in step is shown.</li>
        <li><code>POST /scan/{qr_token}/login</code> validates the code/PIN, regenerates the session ID and sets the scan session.</li>
        <li><code>POST /scan/{qr_token}/time-in</code> / <code>time-out</code> insert an <code>attendance_logs</code> row with the location and timestamp, then flash a result back to the kiosk.</li>
        <li><code>POST /scan/{qr_token}/logout</code> clears the scan session. Admin sessions are untouched.</li>
    </ol>

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
        <tr><td>Security headers</td><td>Global <code>SecurityHeaders</code> middleware: <code>X-Frame-Options: DENY</code>, <code>X-Content-Type-Options: nosniff</code>, <code>Referrer-Policy</code>, <code>Permissions-Policy</code>, and <code>Strict-Transport-Security</code> over HTTPS.</td></tr>
        <tr><td>Brute-force protection</td><td>Rate limits on login, password reset, verification, scan login and time-in/out.</td></tr>
        <tr><td>CSRF</td><td>Laravel CSRF verification on every form; tokens present on all scan and admin forms.</td></tr>
        <tr><td>Credentials</td><td>Passwords and PINs stored as bcrypt hashes (<code>pin_hash</code>).</td></tr>
        <tr><td>Session hygiene</td><td>Session ID regenerated on login; <code>HttpOnly</code> + <code>Secure</code> cookie flags.</td></tr>
        <tr><td>Mass assignment</td><td>All models whitelist fields via <code>#[Fillable]</code>.</td></tr>
        <tr><td>Output escaping</td><td>Blade escapes all output; no raw <code>@verbatim{!! !!}@endverbatim</code> echo sites.</td></tr>
        <tr><td>Dependency audits</td><td><code>composer audit</code> and <code>npm audit</code> report no known vulnerabilities.</td></tr>
    </table>

    <h2>11. Testing</h2>
    <ul>
        <li>PHPUnit feature tests in <code>tests/Feature</code> run against in-memory SQLite.</li>
        <li>Coverage includes admin/manager access control, the scan flow, dashboards, reports, settings docs, and security headers.</li>
        <li>Run with <code>php artisan test</code>; code style with <code>vendor/bin/pint</code>.</li>
    </ul>
</div>
