<x-employee-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Welcome back, {{ $employee->name }}</h1>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex flex-col items-center gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-center">
                    <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-4 ring-primary-500/15 dark:bg-neutral-800">
                        @if ($employee->photo_url)
                            <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-10 w-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @endif
                    </span>
                    <div class="text-center sm:text-left">
                        <h2 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $employee->name }}</h2>
                        <p class="mt-0.5 text-sm text-neutral-500 dark:text-neutral-400">
                            {{ $employee->employee_code }}
                            @if ($employee->department)
                                &middot; {{ $employee->department }}
                            @elseif ($employee->homeLocation)
                                &middot; {{ $employee->homeLocation->name }}
                            @endif
                        </p>
                        @if ($employee->email)
                            <p class="mt-0.5 text-sm text-neutral-400 dark:text-neutral-500">{{ $employee->email }}</p>
                        @endif
                    </div>
                </div>

                <div class="text-center sm:text-right">
                    <span class="badge badge-neutral mb-1">Today</span>
                    @if ($latest?->type === 'time_in')
                        <span class="badge badge-success block">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                            Clocked in since {{ $latest->scanned_at->format('g:i A') }}
                        </span>
                    @else
                        <span class="badge badge-danger block">Not clocked in today</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                @if ($employee->homeLocation)
                    <a href="{{ route('scan.show', $employee->homeLocation->qr_token) }}" class="btn btn-ghost">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Clock In / Out
                    </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="card p-5">
                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $todayLogs->count() }}</div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">Logs today</div>
            </div>
            <div class="card p-5">
                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $daysPresentThisWeek }}</div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">Days present this week</div>
            </div>
            <div class="card p-5">
                <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $monthCount }}</div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">Logs this month</div>
            </div>
            <div class="card p-5">
                <div class="text-2xl font-bold text-neutral-900 dark:text-white">
                    {{ $latest?->type === 'time_in' ? 'On clock' : 'Off clock' }}
                </div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400">Current status</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="card p-6">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Today's activity</h3>
                @if ($todayLogs->isEmpty())
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">No activity recorded yet today.</p>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($todayLogs as $log)
                            <li class="flex items-center justify-between rounded-control border border-neutral-200 px-4 py-3 dark:border-white/10">
                                <span class="flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white">
                                    @if ($log->type === 'time_in')
                                        <svg class="h-4 w-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                    {{ $log->type === 'time_in' ? 'Time In' : 'Time Out' }}
                                    @if ($log->wasAutoClockedOut())
                                        <span class="badge badge-info">Automatic</span>
                                    @endif
                                </span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $log->scanned_at->format('g:i A') }}
                                    @if ($log->location)
                                        &middot; {{ $log->location->name }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent activity</h3>
                    <a href="{{ route('employee.attendance') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">View all</a>
                </div>
                @if ($recentLogs->isEmpty())
                    <p class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">No attendance history yet.</p>
                @else
                    <ul class="mt-4 divide-y divide-neutral-100 dark:divide-white/5">
                        @foreach ($recentLogs as $log)
                            <li class="flex items-center justify-between py-3">
                                <span class="flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white">
                                    @if ($log->type === 'time_in')
                                        <span class="badge badge-success">Time In</span>
                                    @else
                                        <span class="badge badge-neutral">Time Out</span>
                                        @if ($log->wasAutoClockedOut())
                                            <span class="badge badge-info">Automatic</span>
                                        @endif
                                    @endif
                                    {{ $log->scanned_at->format('D, M j') }}
                                </span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ $log->scanned_at->format('g:i A') }}
                                    @if ($log->location)
                                        &middot; {{ $log->location->name }}
                                    @endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-employee-layout>
