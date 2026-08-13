<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Attendance Reports</h2>
            <a href="{{ route('admin.attendance.index') }}" class="btn-link">← Back to Attendance</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $fortnightEnd = $start->copy()->addDays(13);
            @endphp

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
                    <x-input-label for="start" value="Fortnight starting" />
                    <input type="date" id="start" name="start" value="{{ $start->format('Y-m-d') }}" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <x-primary-button class="w-full sm:w-auto">Generate</x-primary-button>

                <div class="ms-auto flex w-full flex-wrap gap-2 sm:w-auto">
                    <a href="{{ route('admin.attendance.reports.download', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn-success">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Excel
                    </a>
                    <a href="{{ route('admin.attendance.reports.download', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn-danger">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        PDF
                    </a>
                </div>
            </form>

            <div class="card">
                <div class="card-header flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Fortnightly hours report</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $start->format('D, M j, Y') }} – {{ $fortnightEnd->format('D, M j, Y') }} · {{ count($workingDays) }} working days (Mon–Fri)</p>
                    </div>
                    <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ count($rows) }} employee(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="table whitespace-nowrap">
                        <thead>
                            <tr class="bg-secondary-900 uppercase text-2xs text-white dark:bg-secondary-900">
                                <th class="px-4 py-3" rowspan="2">Employee</th>
                                <th class="px-4 py-3" rowspan="2">Code</th>
                                <th class="border-s border-secondary-800 px-4 py-3 text-center" colspan="5">Week 1</th>
                                <th class="border-s border-secondary-800 px-4 py-3 text-center" colspan="5">Week 2</th>
                                <th class="border-s border-secondary-800 px-4 py-3 text-center" rowspan="2">Fortnight Hours</th>
                                <th class="border-s border-secondary-800 px-4 py-3 text-center" rowspan="2">Days Worked ({{ count($workingDays) }})</th>
                            </tr>
                            <tr class="bg-secondary-800 uppercase text-2xs text-white dark:bg-secondary-800">
                                @foreach ($workingDays as $day)
                                    <th class="border-s border-secondary-900 px-3 py-2 text-center">{{ $day->format('D') }}<span class="block text-[10px] font-normal text-neutral-300">{{ $day->format('m/d') }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $row['employee']->name }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $row['employee']->employee_code }}</td>
                                    @foreach ($row['days'] as $day)
                                        <td class="px-3 py-3 text-center text-neutral-600 dark:text-neutral-300 {{ $day['present'] ? '' : 'text-neutral-300 dark:text-neutral-600' }}">
                                            {{ $day['present'] ? number_format($day['hours'], 2).'h' : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="border-s border-neutral-200 px-4 py-3 text-center font-semibold text-neutral-900 dark:border-neutral-800 dark:text-white">{{ number_format($row['total_hours'], 2) }}h</td>
                                    <td class="border-s border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-300">{{ $row['days_worked'] }} / {{ count($workingDays) }}</td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="14">No employees found for this period.</td></tr>
                            @endforelse
                        </tbody>
                        @if (count($rows))
                            <tfoot class="bg-neutral-50 font-semibold dark:bg-neutral-800/60">
                                <tr>
                                    <td class="px-4 py-3 text-neutral-800 dark:text-neutral-200" colspan="2">Totals</td>
                                    @foreach ($totals as $total)
                                        <td class="px-3 py-3 text-center text-neutral-800 dark:text-neutral-200">{{ number_format($total, 2) }}h</td>
                                    @endforeach
                                    <td class="border-s border-neutral-200 px-4 py-3 text-center text-neutral-900 dark:border-neutral-800 dark:text-white">{{ number_format(array_sum($totals), 2) }}h</td>
                                    <td class="border-s border-neutral-200 px-4 py-3 text-center text-neutral-600 dark:border-neutral-800 dark:text-neutral-300">—</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
