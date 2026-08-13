<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Edit Attendance Reason</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="card p-5 sm:p-6">
                <form method="POST" action="{{ route('admin.attendance-reasons.update', $reason) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')
                    @include('admin.attendance-reasons._form')

                    <div class="flex items-center gap-3">
                        <x-primary-button>Save changes</x-primary-button>
                        <a href="{{ route('admin.attendance-reasons.index') }}" class="btn-ghost self-center">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
