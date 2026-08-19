<x-employee-layout>
    @php
        $autoTime = $setting->autoClockOutCarbon();
        $lateErrors = $errors->has('late_reason_id') || $errors->has('late_other_reason');
        $earlyErrors = $errors->has('early_clock_out_reason_id') || $errors->has('early_other_reason');

        $isClockedIn = $latest?->type === 'time_in';
        $hasCheckedOut = $latest?->type === 'time_out';
        $autoOut = $hasCheckedOut && ($latest?->wasAutoClockedOut() ?? false);
        $lateToday = $todayLogs->contains(fn ($log) => $log->type === 'time_in' && $log->is_late);

        $shiftStart = \Illuminate\Support\Carbon::parse($setting->shift_start_time)->format('g:i A');
        $shiftEnd = \Illuminate\Support\Carbon::parse($setting->shift_end_time)->format('g:i A');

        $todayTimeIn = $todayLogs->where('type', 'time_in')->sortBy('scanned_at')->first();
        $todayTimeOut = $todayLogs->where('type', 'time_out')->sortByDesc('scanned_at')->first();

        $todayLateReason = $todayTimeIn ? ($todayTimeIn->lateReason?->name ?? $todayTimeIn->late_reason_text) : null;
        $todayEarlyReason = $todayTimeOut ? ($todayTimeOut->earlyClockOutReason?->name ?? $todayTimeOut->early_clock_out_reason_text) : null;
    @endphp

    <div class="mx-auto max-w-xl space-y-5">
        {{-- Header --}}
        <div class="card flex items-center justify-between gap-4 p-5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-4 ring-primary-500/15 dark:bg-neutral-800">
                    @if ($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}" class="h-full w-full object-cover">
                    @else
                        <span class="flex h-full w-full items-center justify-center text-neutral-400">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                    @endif
                </span>
                <div class="min-w-0">
                    <p class="truncate text-lg font-semibold text-neutral-900 dark:text-white">{{ $employee->name }}</p>
                    <p class="truncate text-sm text-neutral-500 dark:text-neutral-400">{{ $location->name }}</p>
                </div>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-2xs font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500">{{ now()->format('l, M j') }}</p>
                <p x-data="{ now: new Date() }" x-init="setInterval(() => now = new Date(), 1000)" class="font-mono text-lg font-semibold leading-tight tabular-nums text-neutral-900 dark:text-white">
                    <span x-text="now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })"></span>
                </p>
            </div>
        </div>

        {{-- Status --}}
        <div class="card p-5 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full {{ $isClockedIn ? 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400' : ($autoOut ? 'bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400' : 'bg-neutral-200 text-neutral-500 dark:bg-white/10 dark:text-neutral-400') }}">
                @if ($isClockedIn)
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @else
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                @endif
            </div>
            <p class="mt-3 text-base font-semibold text-neutral-900 dark:text-white">
                @if ($isClockedIn)
                    Clocked in since {{ $latest->scanned_at->format('g:i A') }}
                @elseif ($autoOut)
                    Auto clocked out at {{ $latest->scanned_at->format('g:i A') }}
                @elseif ($hasCheckedOut)
                    Clocked out at {{ $latest->scanned_at->format('g:i A') }}
                @else
                    Not clocked in today
                @endif
            </p>
            @if ($lateToday)
                <span class="badge badge-warning mt-2">
                    <span class="h-1.5 w-1.5 rounded-full bg-warning-500"></span>
                    Late today
                </span>
            @endif
        </div>

        {{-- Clock In / Clock Out --}}
        <div class="card p-5" x-data="{ showLate: @js($lateErrors), showEarly: @js($earlyErrors) }">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Time In --}}
                <form method="POST" action="{{ route('scan.time-in', $location->qr_token) }}" class="space-y-4">
                    @csrf

                    <button type="submit" aria-label="Clock in"
                        class="flex min-h-[3.5rem] w-full items-center justify-center gap-2 rounded-control py-4 text-lg font-semibold text-white transition-colors duration-150 {{ $isClockedIn ? 'cursor-not-allowed bg-success-300 dark:bg-success-500/30' : 'bg-success-600 shadow-card hover:bg-success-500' }}"
                        @disabled($isClockedIn)>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0118 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3l1.5 1.5 3-3.75" />
                        </svg>
                        <span>
                            @if ($isClockedIn)
                                Clocked In
                            @else
                                Time In
                            @endif
                        </span>
                    </button>

                    @if ($isClockedIn)
                        <div class="rounded-control border border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Reason locked</p>
                                    @if ($todayLateReason)
                                        <p class="mt-1 text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $todayLateReason }}</p>
                                        <span class="badge {{ $todayTimeIn->reason_status === 'approved' ? 'badge-success' : ($todayTimeIn->reason_status === 'rejected' ? 'badge-danger' : 'badge-warning') }} mt-1.5">
                                            {{ $todayTimeIn->reasonStatusLabel() }}
                                        </span>
                                    @else
                                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">A reason can no longer be added after clocking in.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                    <button type="button" @click="showLate = ! showLate"
                        class="flex w-full items-center justify-center gap-2 rounded-control border border-warning-300 bg-warning-50 px-4 py-2 text-sm font-medium text-warning-700 transition-colors duration-150 hover:bg-warning-100 dark:border-warning-700/40 dark:bg-warning-500/10 dark:text-warning-300 dark:hover:bg-warning-500/20">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="showLate ? 'Hide reason' : 'Add reason'"></span>
                    </button>
                    @endif

                    <div x-show="showLate" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
                        <div class="rounded-lg border border-warning-300 bg-warning-50 p-4 dark:border-warning-700/40 dark:bg-warning-500/10" role="group" aria-labelledby="late-reason-title">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <h3 id="late-reason-title" class="text-sm font-semibold text-neutral-900 dark:text-white">Reason for Late Arrival</h3>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-300">
                                        Your clock-in time is after the shift start time of {{ $shiftStart }}.
                                        Adding a reason is optional and will be reviewed by an admin.
                                    </p>
                                </div>
                            </div>

                            <label for="late_reason_id" class="label mt-3">Predefined reason</label>
                            <select id="late_reason_id" name="late_reason_id" class="input mt-1.5 !w-full">
                                <option value="">Select a reason…</option>
                                @foreach ($lateReasons as $reason)
                                    <option value="{{ $reason->id }}" @selected(old('late_reason_id') == $reason->id)>{{ $reason->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('late_reason_id')" class="mt-2" />

                            <label for="late_other_reason" class="label mt-3">Other reason</label>
                            <textarea id="late_other_reason" name="late_other_reason" rows="2"
                                maxlength="{{ $reasonMaxLength }}" placeholder="Describe the reason for your late arrival…"
                                class="input mt-1.5 !w-full">{{ old('late_other_reason') }}</textarea>
                            <x-input-error :messages="$errors->get('late_other_reason')" class="mt-2" />
                        </div>
                    </div>
                </form>

                {{-- Time Out --}}
                <form method="POST" action="{{ route('scan.time-out', $location->qr_token) }}" class="space-y-4">
                    @csrf

                    <button type="submit" aria-label="Clock out"
                        class="flex min-h-[3.5rem] w-full items-center justify-center gap-2 rounded-control py-4 text-lg font-semibold text-white transition-colors duration-150 {{ ! $isClockedIn ? 'cursor-not-allowed bg-danger-300 dark:bg-danger-500/30' : 'bg-danger-600 shadow-card hover:bg-danger-500' }}"
                        @disabled(! $isClockedIn)>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            @if ($isClockedIn)
                                Time Out
                            @else
                                Not Clocked In
                            @endif
                        </span>
                    </button>

                    @if ($isClockedIn)
                        <button type="button" @click="showEarly = ! showEarly"
                            class="flex w-full items-center justify-center gap-2 rounded-control border border-info-300 bg-info-50 px-4 py-2 text-sm font-medium text-info-700 transition-colors duration-150 hover:bg-info-100 dark:border-info-700/40 dark:bg-info-500/10 dark:text-info-300 dark:hover:bg-info-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span x-text="showEarly ? 'Hide reason' : 'Add reason'"></span>
                        </button>
                    @elseif ($hasCheckedOut)
                        <div class="rounded-control border border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Reason locked</p>
                                    @if ($todayEarlyReason)
                                        <p class="mt-1 text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $todayEarlyReason }}</p>
                                        <span class="badge {{ $todayTimeOut->reason_status === 'approved' ? 'badge-success' : ($todayTimeOut->reason_status === 'rejected' ? 'badge-danger' : 'badge-warning') }} mt-1.5">
                                            {{ $todayTimeOut->reasonStatusLabel() }}
                                        </span>
                                    @else
                                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">A reason can no longer be added after clocking out.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div x-show="showEarly" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
                        <div class="rounded-lg border border-info-300 bg-info-50 p-4 dark:border-info-700/40 dark:bg-info-500/10" role="group" aria-labelledby="early-reason-title">
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="min-w-0 flex-1">
                                    <h3 id="early-reason-title" class="text-sm font-semibold text-neutral-900 dark:text-white">Reason for Early Clock-Out</h3>
                                    <p class="mt-0.5 text-xs text-neutral-600 dark:text-neutral-300">
                                        Your clock-out time is before the shift end time of {{ $shiftEnd }}.
                                        Adding a reason is optional and will be reviewed by an admin.
                                    </p>
                                </div>
                            </div>

                            <label for="early_clock_out_reason_id" class="label mt-3">Predefined reason</label>
                            <select id="early_clock_out_reason_id" name="early_clock_out_reason_id" class="input mt-1.5 !w-full">
                                <option value="">Select a reason…</option>
                                @foreach ($earlyReasons as $reason)
                                    <option value="{{ $reason->id }}" @selected(old('early_clock_out_reason_id') == $reason->id)>{{ $reason->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('early_clock_out_reason_id')" class="mt-2" />

                            <label for="early_other_reason" class="label mt-3">Other reason</label>
                            <textarea id="early_other_reason" name="early_other_reason" rows="2"
                                maxlength="{{ $reasonMaxLength }}" placeholder="Describe the reason for your early clock-out…"
                                class="input mt-1.5 !w-full">{{ old('early_other_reason') }}</textarea>
                            <x-input-error :messages="$errors->get('early_other_reason')" class="mt-2" />
                        </div>
                    </div>
                </form>
            </div>

            @if ($setting->auto_clock_out_enabled)
                <p class="mt-4 rounded-control border border-neutral-200 bg-neutral-50 px-4 py-3 text-center text-xs text-neutral-500 dark:border-white/10 dark:bg-white/5 dark:text-neutral-400">
                    <svg class="mx-auto mb-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Employees still clocked in at {{ $autoTime->format('g:i A') }} are automatically clocked out.
                </p>
            @endif
        </div>

        {{-- Shift schedule --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Shift schedule</h2>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-control border border-neutral-200/80 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                    <p class="text-2xs font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Shift start</p>
                    <p class="mt-0.5 text-sm font-semibold text-neutral-900 dark:text-white">{{ $shiftStart }}</p>
                </div>
                <div class="rounded-control border border-neutral-200/80 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                    <p class="text-2xs font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Shift end</p>
                    <p class="mt-0.5 text-sm font-semibold text-neutral-900 dark:text-white">{{ $shiftEnd }}</p>
                </div>
                <div class="rounded-control border border-neutral-200/80 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                    <p class="text-2xs font-medium uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Home location</p>
                    <p class="mt-0.5 truncate text-sm font-semibold text-neutral-900 dark:text-white">{{ $employee->homeLocation?->name ?? $location->name }}</p>
                </div>
            </div>
        </div>

        {{-- Today's activity --}}
        @if ($todayLogs->isNotEmpty())
            <div class="card p-5">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Today's activity</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($todayLogs as $log)
                        <li class="flex items-center justify-between rounded-control border border-neutral-200/80 px-3.5 py-2.5 dark:border-white/10">
                            <span class="flex items-center gap-2 text-sm font-medium text-neutral-900 dark:text-white">
                                @if ($log->type === 'time_in')
                                    <svg class="h-4 w-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Time in
                                @else
                                    <svg class="h-4 w-4 text-danger-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Time out
                                @endif
                                @if ($log->wasAutoClockedOut())
                                    <span class="badge badge-info">Automatic</span>
                                @endif
                            </span>
                            <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $log->scanned_at->format('g:i A') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Recent attendance --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Recent attendance</h2>
                <a href="{{ route('employee.attendance') }}" class="text-sm font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400">View all</a>
            </div>

            @if (empty($history))
                <p class="px-5 pb-6 text-sm text-neutral-500 dark:text-neutral-400">No attendance recorded in the last 7 days.</p>
            @else
                <ul class="divide-y divide-neutral-100 dark:divide-white/5">
                    @foreach ($history as $row)
                        <li class="flex items-center justify-between px-5 py-3.5">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $row['date']->format('D, M j') }}</p>
                                <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $row['time_in']?->format('g:i A') ?? '—' }}
                                    &rarr;
                                    {{ $row['time_out']?->format('g:i A') ?? ($row['on_clock'] ? 'On clock' : '—') }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                @if ($row['is_late'])
                                    <span class="badge badge-warning">Late</span>
                                @endif
                                <span class="text-sm font-semibold tabular-nums text-neutral-900 dark:text-white">{{ $row['hours'] }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-employee-layout>
