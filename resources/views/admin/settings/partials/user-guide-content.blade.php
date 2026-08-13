<div class="doc-body">
    <div class="toc">
        <strong>Contents</strong>
        <ol>
            <li>What this system does</li>
            <li>Signing in</li>
            <li>The dashboard</li>
            <li>Attendance log</li>
            <li>Fortnightly reports</li>
            <li>Managing employees</li>
            <li>Locations &amp; QR codes</li>
            <li>Managing user accounts (admins)</li>
            <li>The scan screen (employee kiosk)</li>
            <li>Your profile &amp; signing out</li>
            <li>Troubleshooting</li>
        </ol>
    </div>

    <h2>1. What this system does</h2>
    <p>
        Smart Attendance is a QR-code-based time tracking system. Employees scan a printed QR code at a
        location, enter their employee code and PIN, then tap <strong>Time In</strong> or
        <strong>Time Out</strong>. Administrators and managers review the resulting attendance records on a
        dashboard, browse the attendance log, and download fortnightly reports.
    </p>
    <p>There are two kinds of accounts:</p>
    <ul>
        <li><strong>Admin</strong> &mdash; full access: dashboard, attendance, reports, employees, locations, users and settings.</li>
        <li><strong>Manager</strong> &mdash; access scoped to one location: dashboard, attendance, reports and employees for that location only.</li>
    </ul>

    <h2>2. Signing in</h2>
    <ol>
        <li>Open the system URL in a browser (you will be given the address by your administrator).</li>
        <li>Enter your <strong>email address</strong> and <strong>password</strong>.</li>
        <li>Click <strong>Log in</strong>. You will land on the dashboard.</li>
    </ol>
    <div class="callout">
        For security, sign-in attempts are limited (6 per minute). If you exceed this, wait a minute before
        trying again. If you forget your password, use the <strong>Forgot your password?</strong> link on the
        sign-in page and follow the email instructions.
    </div>

    <h2>3. The dashboard</h2>
    <p>The dashboard gives an at-a-glance picture of today&rsquo;s attendance. Managers only see their own
    location; admins see all locations.</p>
    <ul>
        <li><strong>KPI cards</strong> &mdash; employees checked in today, currently on the clock, checked out, absent, and total active employees.</li>
        <li><strong>Charts</strong> &mdash; time-in vs time-out volumes, the weekly activity trend, and the status/location breakdown.</li>
        <li><strong>Recent activity</strong> &mdash; the latest scans across the organisation.</li>
    </ul>
    <p>Click any KPI card to open a detail modal listing the matching employees or records. Click
    <strong>Today&rsquo;s Logs</strong> to review the raw scan entries.</p>

    <h2>4. Attendance log</h2>
    <p>The <strong>Attendance</strong> page lists every recorded scan. Use the filters to narrow the list:</p>
    <ul>
        <li><strong>Location</strong> &mdash; admins may filter by location; managers are already limited to their own.</li>
        <li><strong>From / To</strong> &mdash; date range for the scans.</li>
    </ul>
    <p>Use <strong>Export CSV</strong> to download the filtered list for use in spreadsheets.</p>

    <h2>5. Fortnightly reports</h2>
    <p>The <strong>Reports</strong> page generates a two-week (10 working day, Monday&ndash;Friday) report for
    every employee:</p>
    <ul>
        <li>Daily hours worked (from the first <em>time-in</em> to the last <em>time-out</em> on each day).</li>
        <li>Total fortnight hours and days worked (out of 10).</li>
        <li>A totals row across all employees.</li>
    </ul>
    <p>Use the buttons on the report page to download it as an <strong>Excel (.xlsx)</strong> or
    <strong>PDF</strong> file. The report period starts on the Monday of the current fortnight; use the
    <em>start date</em> field to view a different fortnight.</p>

    <h2>6. Managing employees</h2>
    <p>Under <strong>Employees</strong> you can add, edit, archive or delete employee records:</p>
    <ol>
        <li>Click <strong>New Employee</strong> to create a record.</li>
        <li>Enter the employee&rsquo;s <strong>name</strong>, a unique <strong>employee code</strong>, and a
            <strong>PIN</strong> (4&ndash;8 digits). The PIN is what the employee types on the scan screen.</li>
        <li>Optionally set their <strong>home location</strong> and confirm the account is <strong>active</strong>.</li>
    </ol>
    <div class="callout-warn">
        Deleting an employee also deletes their attendance history. Use the <strong>Active</strong> switch to
        disable an employee instead of deleting them.
    </div>

    <h2>7. Locations &amp; QR codes</h2>
    <p>Under <strong>Locations</strong> (admins only), each workplace has its own QR code. To roll out a scan point:</p>
    <ol>
        <li>Create the location (name, address, timezone, active status).</li>
        <li>Open the location and click <strong>QR Code</strong>.</li>
        <li>Print the QR code and display it where employees will scan it.</li>
    </ol>
    <p>If a printed code is lost or compromised, use <strong>Regenerate Token</strong> to issue a new QR code;
    the old code immediately stops working.</p>

    <h2>8. Managing user accounts (admins)</h2>
    <p>Under <strong>Users</strong> (admins only), create accounts for admins and managers. For each account
    choose a <strong>role</strong> and, for managers, the <strong>location</strong> they supervise. You can
    update these details later or delete accounts that are no longer needed. You cannot delete your own account.</p>

    <h2>9. The scan screen (employee kiosk)</h2>
    <p>Employees do not need an account. On the scan screen shown at a location:</p>
    <ol>
        <li>Enter their <strong>employee code</strong> and <strong>PIN</strong> and tap <strong>Sign in</strong>.</li>
        <li>Tap <strong>Time In</strong> when they arrive and <strong>Time Out</strong> when they leave.</li>
        <li>Multiple time-in/time-out pairs per day are allowed; the system stores each scan.</li>
    </ol>
    <p>The sign-in lasts for the browsing session and is limited to the location whose QR code was scanned. Tap
    <strong>Sign out</strong> (or close the browser) to end it.</p>

    <h2>10. Your profile &amp; signing out</h2>
    <p>Use the menu next to your name to open <strong>Profile</strong> (update your name or email, or delete
    your account) or <strong>Log Out</strong> to end the session.</p>

    <h2>11. Troubleshooting</h2>
    <table>
        <tr><th>Problem</th><th>Solution</th></tr>
        <tr><td>&ldquo;Invalid credentials&rdquo; when signing in</td><td>Check the email/password or use the forgot-password flow.</td></tr>
        <tr><td>Employee cannot sign in at a scan point</td><td>Confirm the employee is active and the code/PIN are correct; confirm the location is active.</td></tr>
        <tr><td>The QR code no longer works</td><td>The location&rsquo;s token was regenerated &mdash; print the new QR code from Locations.</td></tr>
        <tr><td>&ldquo;Too many attempts&rdquo; on sign-in</td><td>Wait about a minute and try again.</td></tr>
        <tr><td>Manager cannot see another location&rsquo;s data</td><td>Managers are intentionally limited to their assigned location; an admin can adjust the assignment.</td></tr>
        <tr><td>PDF reports look odd in some viewers</td><td>Download the Excel version instead, or use a recent PDF viewer.</td></tr>
    </table>
</div>
