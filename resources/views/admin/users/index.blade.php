<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Users</h2>
            <a href="{{ route('admin.users.create') }}" class="btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New user
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
                                <th>Email</th>
                                <th>Role</th>
                                <th>Location</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="table-body">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="font-medium text-neutral-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $user->email }}</td>
                                    <td>
                                        <span class="badge {{ $user->role === 'admin' ? 'badge-primary' : 'badge-neutral' }} capitalize">{{ $user->role }}</span>
                                    </td>
                                    <td class="text-neutral-500 dark:text-neutral-400">{{ $user->location?->name ?? '—' }}</td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.users.edit', $user) }}" class="btn-link">Edit</a>
                                            @if ($user->id !== Auth::id())
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                                    onsubmit="return confirm('Remove {{ $user->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-link text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table-empty"><td colspan="5">No users yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
