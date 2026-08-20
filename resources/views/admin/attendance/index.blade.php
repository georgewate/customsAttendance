<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Attendance</h2>
    </x-slot>

    <div class="py-10" x-data="{
        show: false,
        editing: {},
        openEdit(data) {
            this.editing = data;
            this.show = true;
        },
        closeEdit() {
            this.show = false;
            this.editing = {};
        },
    }">
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

            @if ($activeFilter)
                <div class="flex flex-wrap items-center gap-3">
                    <span class="badge badge-info">
                        @switch($activeFilter)
                            @case('late')
                                Showing: Late arrivals
                                @break
                            @case('early')
                                Showing: Early clock-outs
                                @break
                            @case('on-clock')
                                Showing: Employees currently on the clock
                                @break
                        @endswitch
                    </span>
                    <a href="{{ route('admin.attendance.index') }}" class="btn-link">Clear filter</a>
                </div>
            @endif

            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>Employee</th>
                                <th>Location</th>
                                <th>Date</th>
                                <th>Time in</th>
                                <th>Time out</th>
                                @if (Auth::user()->isAdmin())
                                    <th class="text-right">Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($logs as $row)
                                <tr>
                                    <td>
                                        <span class="font-medium text-neutral-900 dark:text-white">{{ $row['employee']->name }}</span>
                                        <span class="text-neutral-400 dark:text-neutral-500">({{ $row['employee']->employee_code }})</span>
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $row['location']->name }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $row['date']->format('M j, Y') }}</td>
                                    <td>
                                        @if ($row['time_in'])
                                            <div class="flex min-w-40 flex-col gap-1.5">
                                                <span class="font-medium text-neutral-900 dark:text-white">{{ $row['time_in']->scanned_at->format('g:i A') }}</span>
                                                @if ($row['time_in']->is_late)
                                                    <span class="badge badge-warning">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                                        Late
                                                    </span>
                                                @endif
                                                @if ($row['time_in']->hasReason())
                                                    <span class="text-xs text-neutral-600 dark:text-neutral-300">{{ $row['time_in']->reasonSummary() }}</span>
                                                    @if ($row['time_in']->reasonIsPending())
                                                        <div class="flex items-center gap-1.5">
                                                            <form method="POST" action="{{ route('admin.attendance.approve-reason', $row['time_in']) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="rounded-control border border-success-300 bg-success-50 px-2 py-1 text-xs font-medium text-success-700 transition-colors hover:bg-success-100 dark:border-success-700/40 dark:bg-success-500/10 dark:text-success-300 dark:hover:bg-success-500/20">
                                                                    Approve
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.attendance.reject-reason', $row['time_in']) }}" class="inline"
                                                                onsubmit="return confirm('Reject this reason? This cannot be undone.');">
                                                                @csrf
                                                                <button type="submit" class="rounded-control border border-danger-300 bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 transition-colors hover:bg-danger-100 dark:border-danger-700/40 dark:bg-danger-500/10 dark:text-danger-300 dark:hover:bg-danger-500/20">
                                                                    Reject
                                                                </button>
                                                            </form>
                                                            <span class="badge badge-warning">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                                                Pending
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="badge {{ $row['time_in']->reason_status === 'approved' ? 'badge-success' : 'badge-danger' }}">
                                                            {{ $row['time_in']->reasonStatusLabel() }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['time_out'])
                                            <div class="flex min-w-40 flex-col gap-1.5">
                                                <span class="font-medium text-neutral-900 dark:text-white">{{ $row['time_out']->scanned_at->format('g:i A') }}</span>
                                                @if ($row['time_out']->wasAutoClockedOut())
                                                    <span class="badge badge-info">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Automatic
                                                    </span>
                                                @endif
                                                @if ($row['time_out']->is_early_clock_out)
                                                    <span class="badge badge-info">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-info-500"></span>
                                                        Early
                                                    </span>
                                                @endif
                                                @if ($row['time_out']->hasReason())
                                                    <span class="text-xs text-neutral-600 dark:text-neutral-300">{{ $row['time_out']->reasonSummary() }}</span>
                                                    @if ($row['time_out']->reasonIsPending())
                                                        <div class="flex items-center gap-1.5">
                                                            <form method="POST" action="{{ route('admin.attendance.approve-reason', $row['time_out']) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="rounded-control border border-success-300 bg-success-50 px-2 py-1 text-xs font-medium text-success-700 transition-colors hover:bg-success-100 dark:border-success-700/40 dark:bg-success-500/10 dark:text-success-300 dark:hover:bg-success-500/20">
                                                                    Approve
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('admin.attendance.reject-reason', $row['time_out']) }}" class="inline"
                                                                onsubmit="return confirm('Reject this reason? This cannot be undone.');">
                                                                @csrf
                                                                <button type="submit" class="rounded-control border border-danger-300 bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 transition-colors hover:bg-danger-100 dark:border-danger-700/40 dark:bg-danger-500/10 dark:text-danger-300 dark:hover:bg-danger-500/20">
                                                                    Reject
                                                                </button>
                                                            </form>
                                                            <span class="badge badge-warning">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                                                                Pending
                                                            </span>
                                                        </div>
                                                    @else
                                                        <span class="badge {{ $row['time_out']->reason_status === 'approved' ? 'badge-success' : 'badge-danger' }}">
                                                            {{ $row['time_out']->reasonStatusLabel() }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-neutral-400 dark:text-neutral-600">—</span>
                                        @endif
                                    </td>
                                    @if (Auth::user()->isAdmin())
                                        <td class="text-right">
                                            <button type="button"
                                                @click="openEdit({
                                                    employee_id: {{ $row['employee']->id }},
                                                    date: {{ Js::from($row['date']->format('Y-m-d')) }},
                                                    action: {{ Js::from(route('admin.attendance.update-day')) }},
                                                    time_in: {{ Js::from($row['time_in']?->scanned_at->format('Y-m-d\TH:i')) }},
                                                    time_in_late_reason_id: {{ Js::from($row['time_in']?->late_reason_id) }},
                                                    time_in_late_other_reason: {{ Js::from($row['time_in']?->late_reason_text) }},
                                                    time_out: {{ Js::from($row['time_out']?->scanned_at->format('Y-m-d\TH:i')) }},
                                                    time_out_early_reason_id: {{ Js::from($row['time_out']?->early_clock_out_reason_id) }},
                                                    time_out_early_other_reason: {{ Js::from($row['time_out']?->early_clock_out_reason_text) }}
                                                })"
                                                class="rounded-control border border-neutral-300 bg-neutral-50 px-2.5 py-1.5 text-xs font-medium text-neutral-700 transition-colors hover:bg-neutral-100 dark:border-white/10 dark:bg-white/5 dark:text-neutral-300 dark:hover:bg-white/10">
                                                Edit
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="{{ Auth::user()->isAdmin() ? 6 : 5 }}">No attendance records for this filter.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $logs->links() }}</div>
        </div>

        @if (Auth::user()->isAdmin())
            <div x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0"
                @keydown.escape.window="closeEdit()">
                <div class="fixed inset-0 bg-neutral-500/75 dark:bg-neutral-950/80" @click="closeEdit()"></div>

                <div class="relative mx-auto mb-6 w-full max-w-lg overflow-hidden rounded-card bg-white p-6 shadow-lift dark:bg-neutral-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">Edit attendance record</h2>
                            <p class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">
                                <span x-text="editing.date"></span>
                            </p>
                        </div>
                        <button type="button" @click="closeEdit()" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" :action="editing.action" class="mt-5 space-y-5">
                        @csrf
                        <input type="hidden" name="employee_id" x-model="editing.employee_id">
                        <input type="hidden" name="date" x-model="editing.date">

                        <div>
                            <x-input-label for="time_in" value="Time in" />
                            <input type="datetime-local" id="time_in" name="time_in"
                                x-model="editing.time_in" class="input mt-1.5 !w-full">
                        </div>

                        <div x-show="editing.time_in">
                            <x-input-label for="time_in_late_reason_id" value="Late arrival reason" />
                            <select id="time_in_late_reason_id" name="time_in_late_reason_id" x-model="editing.time_in_late_reason_id" class="input mt-1.5 !w-full">
                                <option value="">No reason</option>
                                @foreach ($lateReasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                            <label for="time_in_late_other_reason" class="label mt-3">Custom reason</label>
                            <textarea id="time_in_late_other_reason" name="time_in_late_other_reason" rows="2"
                                x-model="editing.time_in_late_other_reason" class="input mt-1.5 !w-full"></textarea>
                        </div>

                        <div class="border-t border-neutral-200 pt-5 dark:border-white/10">
                            <x-input-label for="time_out" value="Time out" />
                            <input type="datetime-local" id="time_out" name="time_out"
                                x-model="editing.time_out" class="input mt-1.5 !w-full">
                        </div>

                        <div x-show="editing.time_out">
                            <x-input-label for="time_out_early_reason_id" value="Early clock-out reason" />
                            <select id="time_out_early_reason_id" name="time_out_early_reason_id" x-model="editing.time_out_early_reason_id" class="input mt-1.5 !w-full">
                                <option value="">No reason</option>
                                @foreach ($earlyReasons as $reason)
                                    <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                                @endforeach
                            </select>
                            <label for="time_out_early_other_reason" class="label mt-3">Custom reason</label>
                            <textarea id="time_out_early_other_reason" name="time_out_early_other_reason" rows="2"
                                x-model="editing.time_out_early_other_reason" class="input mt-1.5 !w-full"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="closeEdit()" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>