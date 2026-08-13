<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">{{ $location->name }} — QR code</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
            <div class="card p-6 text-center">
                @if (session('success'))
                    <div class="alert alert-success mb-4 text-left">{{ session('success') }}</div>
                @endif

                <img src="{{ route('admin.locations.qr', $location) }}" alt="QR code" class="mx-auto mb-4" width="300" height="300">

                <p class="mb-6 break-all text-sm text-neutral-500 dark:text-neutral-400">{{ $location->scanUrl() }}</p>

                <div class="mb-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('admin.locations.qr', $location) }}" download="{{ \Illuminate\Support\Str::slug($location->name) }}-qr.svg"
                        class="btn-primary">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 8h12a2 2 0 002-2V8a2 2 0 00-2-2h-4l-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        Download SVG
                    </a>
                    <a href="{{ route('admin.locations.edit', $location) }}" class="btn-secondary">Edit location</a>
                </div>

                <form method="POST" action="{{ route('admin.locations.regenerate-token', $location) }}"
                    onsubmit="return confirm('Regenerating replaces this QR code — the printed one will stop working. Continue?');">
                    @csrf
                    <button type="submit" class="btn-link text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-500/10">Regenerate QR code (invalidate old one)</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
