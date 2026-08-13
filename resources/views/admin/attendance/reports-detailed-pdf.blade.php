<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Detailed Attendance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 2px; color: #141d30; }
        .meta { color: #64748b; font-size: 9px; margin-bottom: 8px; }
        .summary { margin-bottom: 10px; font-size: 10px; }
        .summary b { margin-right: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 3px 4px; text-align: left; }
        th { background: #1e2b47; color: #ffffff; font-size: 8px; text-transform: uppercase; }
        td { font-size: 8px; }
        .muted { color: #94a3b8; }
        .yes-late { color: #b45309; font-weight: bold; }
        .yes-early { color: #0369a1; font-weight: bold; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>Detailed Attendance Report</h1>
    <div class="meta">
        {{ $from->format('F j, Y') }} &ndash; {{ $to->format('F j, Y') }}
        &nbsp;&middot;&nbsp; {{ count($rows) }} record(s)
        &nbsp;&middot;&nbsp; Generated {{ now()->format('M j, Y g:i A') }}
    </div>

    <div class="summary">
        <b>Records: {{ $summary['records'] }}</b>
        <b>Late arrivals: {{ $summary['late'] }}</b>
        <b>Early clock-outs: {{ $summary['early'] }}</b>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>ID</th>
                <th>Department</th>
                <th>Date</th>
                <th>Clock-In</th>
                <th>Clock-Out</th>
                <th>Hours</th>
                <th>Status</th>
                <th>Late</th>
                <th>Early</th>
                <th>Selected / Custom Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['employee']->name }}</td>
                    <td>{{ $row['employee']->employee_code }}</td>
                    <td>{{ $row['employee']->department ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}</td>
                    <td>{{ $row['time_in']?->format('g:i A') ?? '-' }}</td>
                    <td>{{ $row['time_out']?->format('g:i A') ?? '-' }}</td>
                    <td class="center">{{ $row['hours'] !== null ? number_format($row['hours'], 2) : '-' }}</td>
                    <td>{{ $row['status'] === 'late' ? 'Late' : 'Present' }}</td>
                    <td class="center">{{ $row['is_late'] ? 'Yes' : 'No' }}</td>
                    <td class="center">{{ $row['is_early_clock_out'] ? 'Yes' : 'No' }}</td>
                    <td>
                        @if ($row['is_late'])
                            @if ($row['late_reason'])<span class="yes-late">{{ $row['late_reason'] }}</span>@endif
                            @if ($row['late_reason_text'])<span> {{ $row['late_reason_text'] }}</span>@endif
                        @endif
                        @if ($row['is_early_clock_out'])
                            @if ($row['early_reason'])<span class="yes-early">{{ $row['early_reason'] }}</span>@endif
                            @if ($row['early_reason_text'])<span> {{ $row['early_reason_text'] }}</span>@endif
                        @endif
                        @if (! $row['is_late'] && ! $row['is_early_clock_out'])<span class="muted">-</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11">No attendance records match the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
