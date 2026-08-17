<x-employee-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Attendance History</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ $employee->name }} · {{ $employee->employee_code }}</p>
            </div>
        </div>

        <div class="card p-5">
            <form method="GET" action="{{ route('employee.attendance') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="from" class="label">From</label>
                    <input id="from" name="from" type="date" value="{{ $from }}" max="{{ now()->toDateString() }}" class="input mt-1.5">
                </div>
                <div class="flex-1">
                    <label for="to" class="label">To</label>
                    <input id="to" name="to" type="date" value="{{ $to }}" max="{{ now()->toDateString() }}" class="input mt-1.5">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('employee.attendance') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            <p class="mt-3 text-xs text-neutral-500 dark:text-neutral-400">Showing records up to {{ $maxRangeDays }} days. Employees are marked <span class="font-medium text-warning-600 dark:text-warning-400">Late</span> when clocking in after {{ \Carbon\Carbon::parse($shiftStart)->format('g:i A') }} and marked <span class="font-medium text-info-600 dark:text-info-400">Early</span> when clocking out before {{ \Carbon\Carbon::parse($shiftEnd)->format('g:i A') }}.</p>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-white/10">
                    <thead class="bg-neutral-50 dark:bg-white/5">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Date</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Time In</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Time Out</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Clock-Out Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Total Hours</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Late Arrival</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Early Clock-Out</th>
                            <th scope="col" class="px-4 py-3 text-left text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100 dark:divide-white/5">
                        @forelse ($records as $record)
                            <tr class="hover:bg-neutral-50/70 dark:hover:bg-white/5">
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900 dark:text-white">
                                    {{ $record['date']->format('D, M j, Y') }}
                                    @if ($record['location'])
                                        <span class="block text-xs font-normal text-neutral-400 dark:text-neutral-500">{{ $record['location'] }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ $record['time_in']?->format('g:i A') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ $record['time_out']?->format('g:i A') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($record['clock_out_type'] === 'automatic')
                                        <span class="badge badge-info">Automatic</span>
                                    @elseif ($record['clock_out_type'])
                                        <span class="badge badge-neutral">Manual</span>
                                    @else
                                        <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-700 dark:text-neutral-300">
                                    {{ $record['hours'] ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($record['is_late'])
                                        <span class="badge badge-warning">
                                            <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                            Late
                                        </span>
                                        @if ($record['late_reason'])
                                            <span class="mt-1 block text-xs text-neutral-500 dark:text-neutral-400">{{ $record['late_reason'] }}</span>
                                        @endif
                                        @if ($record['late_reason_text'])
                                            <span class="block text-xs italic text-neutral-500 dark:text-neutral-400">{{ $record['late_reason_text'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($record['is_early_clock_out'])
                                        <span class="badge badge-info">
                                            <span class="h-1.5 w-1.5 rounded-full bg-info-500"></span>
                                            Early
                                        </span>
                                        @if ($record['early_reason'])
                                            <span class="mt-1 block text-xs text-neutral-500 dark:text-neutral-400">{{ $record['early_reason'] }}</span>
                                        @endif
                                        @if ($record['early_reason_text'])
                                            <span class="block text-xs italic text-neutral-500 dark:text-neutral-400">{{ $record['early_reason_text'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($record['status'] === 'present')
                                        <span class="badge badge-success">{{ $record['status_label'] }}</span>
                                    @elseif ($record['status'] === 'late')
                                        <span class="badge badge-warning">{{ $record['status_label'] }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ $record['status_label'] }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">No attendance records found for the selected period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-employee-layout>
