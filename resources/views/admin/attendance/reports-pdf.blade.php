<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; color: #141d30; }
        .meta { color: #64748b; font-size: 10px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 6px; text-align: center; }
        th { background: #1e2b47; color: #ffffff; font-size: 9px; text-transform: uppercase; }
        .emp { text-align: left; width: 22%; }
        .code { width: 8%; }
        .name { text-align: left; font-weight: bold; }
        .muted { color: #94a3b8; }
        tfoot td { background: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Attendance Report</h1>
    <div class="meta">
        {{ $start->format('F j, Y') }} &ndash; {{ $start->copy()->addDays(13)->format('F j, Y') }}
        &nbsp;&middot;&nbsp; {{ count($workingDays) }} working days (Mon&ndash;Fri)
        &nbsp;&middot;&nbsp; Generated {{ now()->format('M j, Y g:i A') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="emp" rowspan="2">Employee</th>
                <th class="code" rowspan="2">Code</th>
                <th colspan="5">Week 1</th>
                <th colspan="5">Week 2</th>
                <th rowspan="2">Fortnight Hours</th>
                <th rowspan="2">Days Worked</th>
            </tr>
            <tr>
                @foreach ($workingDays as $day)
                    <th>{{ $day->format('D m/d') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="name">{{ $row['employee']->name }}</td>
                    <td class="code">{{ $row['employee']->employee_code }}</td>
                    @foreach ($row['days'] as $day)
                        <td class="{{ $day['present'] ? '' : 'muted' }}">{{ $day['present'] ? number_format($day['hours'], 2) : '-' }}</td>
                    @endforeach
                    <td>{{ number_format($row['total_hours'], 2) }}</td>
                    <td>{{ $row['days_worked'] }} / {{ count($workingDays) }}</td>
                </tr>
            @empty
                <tr><td colspan="14">No employees found for this period.</td></tr>
            @endforelse
        </tbody>
        @if (count($rows))
            <tfoot>
                <tr>
                    <td colspan="2">Totals</td>
                    @foreach ($totals as $total)
                        <td>{{ number_format($total, 2) }}</td>
                    @endforeach
                    <td>{{ number_format(array_sum($totals), 2) }}</td>
                    <td>-</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
