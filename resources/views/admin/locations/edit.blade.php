<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Edit {{ $location->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="card p-6">
                <form method="POST" action="{{ route('admin.locations.update', $location) }}">
                    @method('PUT')
                    @include('admin.locations._form')
                </form>
            </div>

            <div class="card p-6">
                <h3 class="mb-2 font-medium text-neutral-900 dark:text-white">Danger zone</h3>
                <form method="POST" action="{{ route('admin.locations.destroy', $location) }}"
                    onsubmit="return confirm('Delete this location? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Delete location</x-danger-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
