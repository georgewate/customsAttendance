<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Locations</h2>
            <a href="{{ route('admin.locations.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New location
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
                                <th>Name</th>
                                <th>Address</th>
                                <th>Employees</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($locations as $location)
                                <tr>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $location->name }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $location->address }}</td>
                                    <td>{{ $location->employees_count }}</td>
                                    <td>
                                        @if ($location->active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-neutral">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.locations.show', $location) }}" class="btn-link">QR code</a>
                                            <a href="{{ route('admin.locations.edit', $location) }}" class="btn-link">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="5">No locations yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $locations->links() }}</div>
        </div>
    </div>
</x-app-layout>
