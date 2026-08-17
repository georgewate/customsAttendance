<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Detailed Attendance Report</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.attendance.reports') }}" class="btn-link">← Fortnightly report</a>
                <a href="{{ route('admin.attendance.index') }}" class="btn-link">← Attendance</a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" class="card flex flex-wrap items-end gap-4 p-4 sm:p-5">
                @if (Auth::user()->isAdmin())
                    <div class="w-full sm:w-auto">
                        <x-input-label for="location_id" value="Location" />
                        <select id="location_id" name="location_id" class="input mt-1.5 !w-full sm:!w-auto">
                            <option value="">All locations</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="w-full sm:w-auto">
                    <x-input-label for="employee_id" value="Employee" />
                    <select id="employee_id" name="employee_id" class="input mt-1.5 !w-full sm:!w-auto">
                        <option value="">All employees</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>{{ $employee->name }} ({{ $employee->employee_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="department" value="Department" />
                    <select id="department" name="department" class="input mt-1.5 !w-full sm:!w-auto">
                        <option value="">All departments</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department }}" @selected(request('department') === $department)>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="from" value="From" />
                    <input type="date" id="from" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="to" value="To" />
                    <input type="date" id="to" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="reason_id" value="Reason" />
                    <select id="reason_id" name="reason_id" class="input mt-1.5 !w-full sm:!w-auto">
                        <option value="">Any reason</option>
                        @foreach ($reasons as $reason)
                            <option value="{{ $reason->id }}" @selected(request('reason_id') == $reason->id)>{{ $reason->name }} ({{ $reason->categoryLabel() }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="search" value="Search" />
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        placeholder="Name or employee ID" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <div class="flex w-full flex-wrap gap-x-4 gap-y-2 sm:w-auto sm:items-end">
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="late_only" value="1" class="checkbox" @checked(request('late_only'))>
                        <span class="text-sm text-neutral-600 dark:text-neutral-300">Late arrivals only</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2">
                        <input type="checkbox" name="early_only" value="1" class="checkbox" @checked(request('early_only'))>
                        <span class="text-sm text-neutral-600 dark:text-neutral-300">Early clock-outs only</span>
                    </label>
                </div>

                <x-primary-button>Apply filters</x-primary-button>
                <a href="{{ route('admin.attendance.reports.detailed') }}" class="btn-secondary">Reset</a>

                <div class="ms-auto flex w-full flex-wrap gap-2 sm:w-auto">
                    <a href="{{ route('admin.attendance.reports.detailed.download', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn-success">Excel</a>
                    <a href="{{ route('admin.attendance.reports.detailed.download', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn-secondary">CSV</a>
                    <a href="{{ route('admin.attendance.reports.detailed.download', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn-danger">PDF</a>
                </div>
            </form>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card p-5">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $summary['records'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Attendance records</div>
                </div>
                <div class="card p-5">
                    <div class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $summary['late'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Late arrivals</div>
                </div>
                <div class="card p-5">
                    <div class="text-2xl font-bold text-info-600 dark:text-info-400">{{ $summary['early'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Early clock-outs</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Detailed records</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $from->format('M j, Y') }} – {{ $to->format('M j, Y') }} · {{ count($rows) }} record(s)
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table whitespace-nowrap">
                        <thead class="table-head">
                            <tr>
                                <th>Employee</th>
                                <th>ID</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Clock-In</th>
                                <th>Clock-Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                                <th>Late Arrival</th>
                                <th>Early Clock-Out</th>
                                <th>Selected / Custom Reason</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $row['employee']->name }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $row['employee']->employee_code }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $row['employee']->department ?? '—' }}</td>
                                    <td class="text-neutral-600 dark:text-neutral-300">{{ \Carbon\Carbon::parse($row['date'])->format('D, M j, Y') }}</td>
                                    <td class="text-neutral-600 dark:text-neutral-300">{{ $row['time_in']?->format('g:i A') ?? '—' }}</td>
                                    <td class="text-neutral-600 dark:text-neutral-300">{{ $row['time_out']?->format('g:i A') ?? '—' }}</td>
                                    <td class="text-neutral-600 dark:text-neutral-300">{{ $row['hours'] !== null ? number_format($row['hours'], 2).'h' : '—' }}</td>
                                    <td>
                                        @if ($row['status'] === 'late')
                                            <span class="badge badge-warning">Late</span>
                                        @else
                                            <span class="badge badge-success">Present</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['is_late'])
                                            <span class="badge badge-warning"><span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>Yes</span>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['is_early_clock_out'])
                                            <span class="badge badge-info"><span class="h-1.5 w-1.5 rounded-full bg-info-500"></span>Yes</span>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">No</span>
                                        @endif
                                    </td>
                                    <td class="max-w-xs text-neutral-600 dark:text-neutral-300">
                                        @if ($row['is_late'])
                                            @if ($row['late_reason'])
                                                <span class="block text-xs font-medium text-warning-700 dark:text-warning-400">{{ $row['late_reason'] }}</span>
                                            @endif
                                            @if ($row['late_reason_text'])
                                                <span class="block truncate text-xs italic" title="{{ $row['late_reason_text'] }}">{{ $row['late_reason_text'] }}</span>
                                            @endif
                                        @endif
                                        @if ($row['is_early_clock_out'])
                                            @if ($row['early_reason'])
                                                <span class="block text-xs font-medium text-info-700 dark:text-info-400">{{ $row['early_reason'] }}</span>
                                            @endif
                                            @if ($row['early_reason_text'])
                                                <span class="block truncate text-xs italic" title="{{ $row['early_reason_text'] }}">{{ $row['early_reason_text'] }}</span>
                                            @endif
                                        @endif
                                        @if (! $row['is_late'] && ! $row['is_early_clock_out'])
                                            <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="11">No attendance records match the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
