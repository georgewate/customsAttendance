<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Employees</h2>
            <a href="{{ route('admin.employees.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                Add employee
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="alert alert-success mb-4">{{ session('success') }}</div>
            @endif

            <div class="card">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead class="table-head">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $employee->employee_code }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                                                @if ($employee->photo_url)
                                                    <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}" class="h-full w-full object-cover">
                                                @else
                                                    <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                @endif
                                            </span>
                                            <span class="font-medium text-neutral-900 dark:text-white">{{ $employee->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $employee->homeLocation?->name }}</td>
                                    <td>
                                        @if ($employee->active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-neutral">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-link">Edit</a>
                                            <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}"
                                                onsubmit="return confirm('Remove {{ $employee->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-link text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="5">No employees yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $employees->links() }}</div>
        </div>
    </div>
</x-app-layout>
