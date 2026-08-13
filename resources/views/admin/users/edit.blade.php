<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Edit {{ $user->name }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <div class="card p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @method('PUT')
                    @include('admin.users._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
