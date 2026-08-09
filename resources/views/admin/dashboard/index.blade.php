<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Dashboard</h2>
            <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </x-slot>

    @php
        $chartData = [
            'labels' => array_column($trend, 'label'),
            'ins' => array_column($trend, 'in'),
            'outs' => array_column($trend, 'out'),
            'totals' => array_column($trend, 'total'),
            'statusLabels' => ['On the clock', 'Checked out', 'Absent'],
            'statusData' => [$status['on_clock'], $status['checked_out'], $status['absent']],
            'locationLabels' => array_column($locations, 'name'),
            'locationData' => array_column($locations, 'present'),
        ];
        $maxPresent = max(array_column($locations, 'present') ?: [0]) ?: 1;
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            {{-- KPI cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <button type="button" @click="$dispatch('open-modal', 'card-checked-in')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-primary-500 to-primary-700 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $checkedInToday }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Checked in today</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>

                <button type="button" @click="$dispatch('open-modal', 'card-on-clock')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-secondary-600 to-secondary-800 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $onClockNow }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">On the clock now</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>

                <button type="button" @click="$dispatch('open-modal', 'card-rate')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-success-500 to-success-700 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $attendanceRate }}%</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Attendance rate</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>

                <button type="button" @click="$dispatch('open-modal', 'card-employees')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-info-500 to-info-700 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $activeEmployees }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Active employees</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>

                <button type="button" @click="$dispatch('open-modal', 'card-locations')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-primary-500 to-secondary-700 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $totalLocations }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Locations</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>

                <button type="button" @click="$dispatch('open-modal', 'card-logs-today')" class="card card-hover group p-5 text-left focus:outline-none">
                    <div class="flex items-center gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-control bg-gradient-to-br from-warning-500 to-warning-700 shadow-card">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </span>
                        <div class="flex-1">
                            <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $logsToday }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Logs today</div>
                        </div>
                        <svg class="h-4 w-4 text-neutral-300 transition-colors group-hover:text-primary-500 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </div>
                </button>
            </div>

            {{-- Charts row 1: trend bar + status doughnut --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="card p-5 lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Time in / Time out</h3>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Last 7 days</span>
                    </div>
                    <div class="relative h-64">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Employee status today</h3>
                    <div class="relative h-64">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Charts row 2: activity line + location doughnut --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="card p-5 lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Attendance activity</h3>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">Last 7 days</span>
                    </div>
                    <div class="relative h-64">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="mb-4 text-sm font-semibold text-neutral-900 dark:text-white">Present today by location</h3>
                    @forelse ($locations as $location)
                        <div class="mb-3 last:mb-0">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-neutral-700 dark:text-neutral-200">{{ $location['name'] }}</span>
                                <span class="text-neutral-500 dark:text-neutral-400">{{ $location['present'] }}</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                                <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-700 transition-all duration-300"
                                     style="width: {{ round($location['present'] / $maxPresent * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">No locations yet.</p>
                    @endforelse
                    <div class="relative mt-4 h-48">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="card">
                <div class="card-header flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent activity</h3>
                    <a href="{{ route('admin.attendance.index') }}" class="btn-link">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>Employee</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>When</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($recentLogs as $log)
                                <tr>
                                    <td>
                                        <span class="font-medium text-neutral-900 dark:text-white">{{ $log->employee->name }}</span>
                                        <span class="text-neutral-400 dark:text-neutral-500">({{ $log->employee->employee_code }})</span>
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $log->location->name }}</td>
                                    <td>
                                        @if ($log->type === 'time_in')
                                            <span class="badge badge-success">
                                                <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>Time in
                                            </span>
                                        @else
                                            <span class="badge badge-danger">
                                                <span class="h-1.5 w-1.5 rounded-full bg-danger-500"></span>Time out
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $log->scanned_at->format('M j, Y g:i A') }}</td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="4">No attendance activity yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card detail modals --}}
    <x-modal name="card-checked-in" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Checked in today ({{ $checkedInToday }})</h3>
            <button @click="$dispatch('close-modal', 'card-checked-in')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($checkedInEmployees as $emp)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $emp['name'] }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['code'] }}</div>
                        </div>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['time'] }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-neutral-500 dark:text-neutral-400">Nobody has checked in yet today.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <x-modal name="card-on-clock" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">On the clock now ({{ $onClockNow }})</h3>
            <button @click="$dispatch('close-modal', 'card-on-clock')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($onClockEmployees as $emp)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $emp['name'] }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['code'] }}</div>
                        </div>
                        <span class="badge badge-success"><span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>{{ $emp['time'] }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-neutral-500 dark:text-neutral-400">Nobody is currently on the clock.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <x-modal name="card-rate" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Attendance rate ({{ $attendanceRate }}%)</h3>
            <button @click="$dispatch('close-modal', 'card-rate')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4 grid grid-cols-3 gap-3">
                <div class="rounded-lg bg-neutral-50 py-3 text-center dark:bg-neutral-800">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $status['on_clock'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">On the clock</div>
                </div>
                <div class="rounded-lg bg-neutral-50 py-3 text-center dark:bg-neutral-800">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $status['checked_out'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Checked out</div>
                </div>
                <div class="rounded-lg bg-neutral-50 py-3 text-center dark:bg-neutral-800">
                    <div class="text-2xl font-bold text-neutral-900 dark:text-white">{{ $status['absent'] }}</div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Absent today</div>
                </div>
            </div>

            <h4 class="mb-1 text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">On the clock</h4>
            <ul class="mb-4 divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($onClockEmployees as $emp)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-sm text-neutral-800 dark:text-neutral-200">{{ $emp['name'] }}</span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['time'] }}</span>
                    </li>
                @empty
                    <li class="py-2 text-sm text-neutral-500 dark:text-neutral-400">None</li>
                @endforelse
            </ul>

            <h4 class="mb-1 text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Checked out</h4>
            <ul class="mb-4 divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($checkedOutEmployees as $emp)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-sm text-neutral-800 dark:text-neutral-200">{{ $emp['name'] }}</span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['time'] }}</span>
                    </li>
                @empty
                    <li class="py-2 text-sm text-neutral-500 dark:text-neutral-400">None</li>
                @endforelse
            </ul>

            <h4 class="mb-1 text-2xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Absent today</h4>
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($absentEmployees as $emp)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-sm text-neutral-800 dark:text-neutral-200">{{ $emp['name'] }}</span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['code'] }}</span>
                    </li>
                @empty
                    <li class="py-2 text-sm text-neutral-500 dark:text-neutral-400">Everyone has logged in today.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <x-modal name="card-employees" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Active employees ({{ $activeEmployees }})</h3>
            <button @click="$dispatch('close-modal', 'card-employees')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($activeEmployeeList as $emp)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $emp['name'] }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $emp['code'] }}</div>
                        </div>
                        <span class="badge badge-neutral">{{ $emp['location'] ?? 'No location' }}</span>
                    </li>
                @empty
                    <li class="py-3 text-sm text-neutral-500 dark:text-neutral-400">No active employees yet.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <x-modal name="card-locations" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Locations ({{ $totalLocations }})</h3>
            <button @click="$dispatch('close-modal', 'card-locations')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($locations as $location)
                    <li class="py-3">
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-neutral-900 dark:text-white">{{ $location['name'] }}</span>
                            <span class="text-neutral-500 dark:text-neutral-400">{{ $location['present'] }} present</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-neutral-100 dark:bg-neutral-800">
                            <div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-primary-700" style="width: {{ round($location['present'] / $maxPresent * 100) }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="py-3 text-sm text-neutral-500 dark:text-neutral-400">No locations yet.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <x-modal name="card-logs-today" :show="false" maxWidth="2xl" focusable>
        <div class="flex items-center justify-between border-b border-neutral-200 bg-neutral-50 px-6 py-4 dark:border-neutral-800 dark:bg-neutral-800/60">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Logs today ({{ $logsToday }})</h3>
            <button @click="$dispatch('close-modal', 'card-logs-today')" class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse ($todayLogs as $log)
                    <li class="flex items-center justify-between py-3">
                        <div>
                            <div class="text-sm font-medium text-neutral-900 dark:text-white">{{ $log['name'] }}</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $log['code'] }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if ($log['type'] === 'time_in')
                                <span class="badge badge-success">Time in</span>
                            @else
                                <span class="badge badge-danger">Time out</span>
                            @endif
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $log['time'] }}</span>
                        </div>
                    </li>
                @empty
                    <li class="py-3 text-sm text-neutral-500 dark:text-neutral-400">No attendance logs yet today.</li>
                @endforelse
            </ul>
        </div>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const data = @json($chartData);

            const themeColors = {
                light: { default: '#64748b', grid: 'rgba(148, 163, 184, 0.25)', card: '#ffffff', absent: '#e2e8f0' },
                dark: { default: '#99a3b4', grid: 'rgba(148, 163, 184, 0.12)', card: '#0f1628', absent: '#334155' },
            };

            function buildCharts() {
                const isDark = document.documentElement.classList.contains('dark');
                const t = themeColors[isDark ? 'dark' : 'light'];

                Chart.defaults.color = t.default;
                Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

                new Chart(document.getElementById('trendChart'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Time in', data: data.ins, backgroundColor: '#794ef0', hoverBackgroundColor: '#6c3edc', borderRadius: 5, maxBarThickness: 34 },
                            { label: 'Time out', data: data.outs, backgroundColor: '#15bce0', hoverBackgroundColor: '#0b97c2', borderRadius: 5, maxBarThickness: 34 },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { color: t.grid }, ticks: { precision: 0 } },
                        },
                    },
                });

                new Chart(document.getElementById('activityChart'), {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Logs',
                            data: data.totals,
                            borderColor: '#794ef0',
                            backgroundColor: 'rgba(121, 78, 240, 0.18)',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: '#794ef0',
                            pointBorderColor: isDark ? '#0f1628' : '#ffffff',
                            pointRadius: 4,
                            pointHoverRadius: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { color: t.grid }, ticks: { precision: 0 } },
                        },
                    },
                });

                new Chart(document.getElementById('statusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.statusLabels,
                        datasets: [{
                            data: data.statusData,
                            backgroundColor: ['#794ef0', '#15bce0', t.absent],
                            hoverBackgroundColor: ['#6c3edc', '#7a4ef0', t.absent],
                            borderWidth: 3,
                            borderColor: t.card,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
                    },
                });

                new Chart(document.getElementById('locationChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.locationLabels,
                        datasets: [{
                            data: data.locationData,
                            backgroundColor: ['#794ef0', '#15bce0', '#0ea5e9', '#10b981', '#f59e0b', '#94a3b8'],
                            hoverBackgroundColor: ['#6c3edc', '#0b97c2', '#0284c7', '#059669', '#d97706', '#64748b'],
                            borderWidth: 3,
                            borderColor: t.card,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
                    },
                });
            }

            function destroyCharts() {
                Object.values(Chart.instances).forEach(function (chart) { chart.destroy(); });
            }

            buildCharts();
            document.addEventListener('themechange', function () {
                destroyCharts();
                buildCharts();
            });
        });
    </script>
</x-app-layout>
