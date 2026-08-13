<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Attendance Reasons</h2>
            <a href="{{ route('admin.attendance-reasons.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New reason
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" class="card flex flex-wrap items-end gap-4 p-4 sm:p-5">
                <div class="w-full sm:w-auto">
                    <x-input-label for="category" value="Category" />
                    <select id="category" name="category" class="input mt-1.5 !w-full sm:!w-auto">
                        <option value="">All categories</option>
                        @foreach (\App\Models\AttendanceReason::CATEGORIES as $value => $label)
                            <option value="{{ $value }}" @selected(request('category') == $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-auto">
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" class="input mt-1.5 !w-full sm:!w-auto">
                        <option value="">All statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="w-full flex-1 sm:w-auto">
                    <x-input-label for="search" value="Search" />
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        placeholder="Search by name or description" class="input mt-1.5 !w-full sm:!w-auto">
                </div>

                <x-primary-button>Filter</x-primary-button>
                <a href="{{ route('admin.attendance-reasons.index') }}" class="btn-secondary">Reset</a>
            </form>

            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created Date</th>
                                <th>Updated Date</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($reasons as $reason)
                                <tr>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $reason->id }}</td>
                                    <td>
                                        @if ($reason->category === \App\Models\AttendanceReason::CATEGORY_LATE)
                                            <span class="badge badge-warning">Late Clock-In</span>
                                        @else
                                            <span class="badge badge-info">Early Clock-Out</span>
                                        @endif
                                    </td>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $reason->name }}</td>
                                    <td class="max-w-[10rem] truncate text-neutral-500 dark:text-neutral-400" title="{{ $reason->description }}">{{ $reason->description ?? '—' }}</td>
                                    <td>
                                        @if ($reason->active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-neutral">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $reason->creator?->name ?? 'System' }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $reason->created_at?->format('M j, Y') ?? '—' }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $reason->updated_at?->format('M j, Y') ?? '—' }}</td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.attendance-reasons.edit', $reason) }}" class="btn-link">Edit</a>

                                            <form method="POST" action="{{ route('admin.attendance-reasons.toggle', $reason) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn-link text-warning-600 dark:text-warning-400">
                                                    {{ $reason->active ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.attendance-reasons.destroy', $reason) }}"
                                                onsubmit="return confirm('Delete reason &quot;{{ $reason->name }}&quot;? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-link text-danger-600 dark:text-danger-500">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="9">No attendance reasons found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $reasons->links() }}</div>
        </div>
    </div>
</x-app-layout>
