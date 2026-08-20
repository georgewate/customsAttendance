<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Audit Log</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="card p-5 sm:p-6 mb-6">
                <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <x-input-label for="action" value="Action" />
                        <select id="action" name="action" class="input mt-1.5 !w-full">
                            <option value="">All actions</option>
                            @foreach ($actions as $action)
                                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="from" value="From" />
                        <input id="from" name="from" type="date" value="{{ request('from') }}" class="input mt-1.5 !w-full">
                    </div>

                    <div>
                        <x-input-label for="to" value="To" />
                        <input id="to" name="to" type="date" value="{{ request('to') }}" class="input mt-1.5 !w-full">
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            Filter
                        </button>
                        @if (request()->hasAny(['action', 'from', 'to']))
                            <a href="{{ route('admin.audit-logs.index') }}" class="btn-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>When</th>
                                <th>Action</th>
                                <th>Actor</th>
                                <th>Description</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="whitespace-nowrap text-neutral-500 dark:text-neutral-400">{{ $log->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                    <td><span class="badge badge-neutral font-mono">{{ $log->action }}</span></td>
                                    <td class="text-neutral-700 dark:text-neutral-200">
                                        @if ($log->user)
                                            {{ $log->user->name }} <span class="text-xs text-neutral-400">(admin)</span>
                                        @elseif ($log->employee)
                                            {{ $log->employee->name }} <span class="text-xs text-neutral-400">(employee)</span>
                                        @else
                                            <span class="text-neutral-400">System</span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-700 dark:text-neutral-200">{{ $log->description }}</td>
                                    <td>
                                        @if ($log->metadata)
                                            <details class="text-xs">
                                                <summary class="cursor-pointer text-primary-600 hover:text-primary-500 dark:text-primary-400">View</summary>
                                                <pre class="mt-1 max-w-md overflow-x-auto rounded-control bg-neutral-50 p-2 dark:bg-white/5"><code>{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                            </details>
                                        @else
                                            <span class="text-neutral-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="5">No audit entries found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>