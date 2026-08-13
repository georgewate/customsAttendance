<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Attendance</h2>
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
                    <x-input-label for="from" value="From" />
                    <input type="date" id="from" name="from" value="{{ request('from') }}" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="to" value="To" />
                    <input type="date" id="to" name="to" value="{{ request('to') }}" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <x-primary-button class="w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filter
                </x-primary-button>
                <a href="{{ route('admin.attendance.export', request()->query()) }}" class="btn-secondary w-full sm:w-auto">Export CSV</a>
                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.attendance.settings') }}" class="btn-secondary w-full sm:w-auto">Attendance Settings</a>
                @endif
            </form>

            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>Employee</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Clock-out type</th>
                                <th>When</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($logs as $log)
                                <tr>
                                    <td>
                                        <span class="font-medium text-neutral-900 dark:text-white">{{ $log->employee->name }}</span>
                                        <span class="text-neutral-400 dark:text-neutral-500">({{ $log->employee->employee_code }})</span>
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $log->location->name }}</td>
                                    <td>
                                        @if ($log->type === 'time_in')
                                            <span class="badge badge-success">Time in</span>
                                        @else
                                            <span class="badge badge-danger">Time out</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->type === 'time_out')
                                            @if ($log->wasAutoClockedOut())
                                                <span class="badge badge-info">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Automatic
                                                </span>
                                            @else
                                                <span class="badge badge-neutral">Manual</span>
                                            @endif
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $log->scanned_at->format('M j, Y g:i A') }}</td>
                                    <td>
                                        @if ($log->hasReason())
                                            <div class="flex min-w-40 flex-col gap-2">
                                                <span class="text-xs text-neutral-600 dark:text-neutral-300">{{ $log->reasonSummary() }}</span>

                                                @if ($log->reason_status === 'pending')
                                                    <div class="flex items-center gap-1.5">
                                                        <form method="POST" action="{{ route('admin.attendance.approve-reason', $log) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" class="rounded-control border border-success-300 bg-success-50 px-2 py-1 text-xs font-medium text-success-700 transition-colors hover:bg-success-100 dark:border-success-700/40 dark:bg-success-500/10 dark:text-success-300 dark:hover:bg-success-500/20">
                                                                Approve
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('admin.attendance.reject-reason', $log) }}" class="inline"
                                                            onsubmit="return confirm('Reject this reason? This cannot be undone.');">
                                                            @csrf
                                                            <button type="submit" class="rounded-control border border-danger-300 bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 transition-colors hover:bg-danger-100 dark:border-danger-700/40 dark:bg-danger-500/10 dark:text-danger-300 dark:hover:bg-danger-500/20">
                                                                Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <span class="badge badge-warning">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                                        Pending
                                                    </span>
                                                @else
                                                    <span class="badge {{ $log->reason_status === 'approved' ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $log->reasonStatusLabel() }}
                                                    </span>
                                                    @if ($log->reason_review_note)
                                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Note: {{ $log->reason_review_note }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="6">No attendance records for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
