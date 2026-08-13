<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Attendance Settings</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="card p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Shift Schedule</h3>
                <p class="mt-1 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                    The shift start and end times define when an employee is considered <span class="font-medium text-warning-600 dark:text-warning-400">late</span>
                    (clocking in after start) or <span class="font-medium text-info-600 dark:text-info-400">early</span> (clocking out before end).
                    A reason is required whenever a clock-in or clock-out falls outside these times.
                </p>

                <form method="POST" action="{{ route('admin.attendance.settings.update') }}" class="mt-6 space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div>
                            <x-input-label for="shift_start_time" value="Shift start time" />
                            <input id="shift_start_time" name="shift_start_time" type="time"
                                value="{{ old('shift_start_time', $setting->shift_start_time) }}"
                                class="input mt-1.5 !w-full"
                                required>
                            <x-input-error :messages="$errors->get('shift_start_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="shift_end_time" value="Shift end time" />
                            <input id="shift_end_time" name="shift_end_time" type="time"
                                value="{{ old('shift_end_time', $setting->shift_end_time) }}"
                                class="input mt-1.5 !w-full"
                                required>
                            <x-input-error :messages="$errors->get('shift_end_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="reason_text_max_length" value="Max custom reason length" />
                            <input id="reason_text_max_length" name="reason_text_max_length" type="number" min="10" max="2000"
                                value="{{ old('reason_text_max_length', $setting->reason_text_max_length) }}"
                                class="input mt-1.5 !w-full"
                                required>
                            <x-input-error :messages="$errors->get('reason_text_max_length')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <x-input-label for="auto_clock_out_time" value="Auto clock-out time" />
                            <input id="auto_clock_out_time" name="auto_clock_out_time" type="time"
                                value="{{ old('auto_clock_out_time', $setting->auto_clock_out_time) }}"
                                class="input mt-1.5 !w-full"
                                required>
                            <x-input-error :messages="$errors->get('auto_clock_out_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label value="Enable auto clock-out" />
                            <label class="mt-3 flex cursor-pointer items-center gap-3">
                                <input id="auto_clock_out_enabled" name="auto_clock_out_enabled" type="checkbox" value="1"
                                    class="checkbox"
                                    {{ old('auto_clock_out_enabled', $setting->auto_clock_out_enabled) ? 'checked' : '' }}>
                                <span class="text-sm text-neutral-600 dark:text-neutral-300">Enabled</span>
                            </label>
                        </div>
                    </div>

                    <div class="rounded-lg border border-neutral-200 p-4 text-sm dark:border-neutral-800">
                        @php
                            $autoTime = $setting->autoClockOutCarbon();
                            $formattedTime = $autoTime->format('g:i A');
                        @endphp
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Shift: </span>
                                <span class="font-semibold text-neutral-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($setting->shift_start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($setting->shift_end_time)->format('g:i A') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Custom reason limit: </span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $setting->reason_text_max_length }} chars</span>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Auto clock-out time: </span>
                                <span class="font-semibold text-neutral-900 dark:text-white">{{ $formattedTime }}</span>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Status: </span>
                                @if ($setting->auto_clock_out_enabled)
                                    <span class="badge badge-success">Enabled</span>
                                @else
                                    <span class="badge badge-neutral">Disabled</span>
                                @endif
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Last updated: </span>
                                <span class="font-medium text-neutral-900 dark:text-white">
                                    {{ $setting->updated_at?->format('M j, Y g:i A') ?? 'Never' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-neutral-500 dark:text-neutral-400">Updated by: </span>
                                <span class="font-medium text-neutral-900 dark:text-white">
                                    {{ $setting->updater?->name ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4-3 3m0 0-3-3m3 3V4" />
                            </svg>
                            Save Settings
                        </x-primary-button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn-ghost self-center">Back to Attendance</a>
                    </div>
                </form>
            </div>

            <div class="card p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">How automatic clock-out works</h3>
                <ul class="mt-3 list-inside space-y-2 text-sm text-neutral-600 dark:text-neutral-300">
                    <li class="flex gap-2"><span>&bull;</span> Employees still clocked in at the configured time are automatically clocked out.</li>
                    <li class="flex gap-2"><span>&bull;</span> A manual clock-out always uses the employee's actual clock-out time and is never overwritten.</li>
                    <li class="flex gap-2"><span>&bull;</span> Automatically clocked-out records are labelled <span class="badge badge-info">Automatic</span>; manual records are labelled <span class="badge badge-neutral">Manual</span>.</li>
                    <li class="flex gap-2"><span>&bull;</span> Every automatic clock-out is recorded in the audit log.</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>