<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-neutral-900 dark:text-white">Settings</h2>
    </x-slot>

    <div class="py-10" x-data="{ tab: 'api' }">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2">
                @php
                    $tabs = [
                        'api' => 'API Docs',
                        'guide' => 'User Guide',
                        'tech' => 'Technical Docs',
                    ];
                @endphp
                @foreach ($tabs as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'bg-primary-600 text-white shadow-card' : 'bg-white text-neutral-600 hover:bg-neutral-100 dark:bg-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800'"
                            class="rounded-control px-4 py-2 text-sm font-medium transition-colors duration-150 focus:outline-none">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @php
                $downloadButton = 'btn-secondary';
                $downloadIcon = 'h-4 w-4';
            @endphp

            {{-- API Docs --}}
            <div x-show="tab === 'api'" x-cloak>
                <div class="card p-5 sm:p-6 mb-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">API &amp; Integration Connection Points</h3>
                            <p class="mt-1 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                                This app doesn't expose a token-based JSON API &mdash; every connection point below is a
                                session/cookie-authenticated web route. The reference below is meant for anyone wiring up QR
                                provisioning tools, kiosk devices, or attendance export automation against this app.
                            </p>
                        </div>
                        <a href="{{ route('admin.settings.api-docs.pdf') }}" class="{{ $downloadButton }}">
                            <svg class="{{ $downloadIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 8h12a2 2 0 002-2V8a2 2 0 00-2-2h-4l-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Download PDF
                        </a>
                    </div>
                </div>

                @foreach ($groups as $group)
                    <div class="card p-5 sm:p-6 mb-6">
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $group['name'] }}</h3>
                        <p class="mt-1 mb-4 text-xs text-neutral-500 dark:text-neutral-400">{{ $group['note'] }}</p>

                        <div class="space-y-3">
                            @foreach ($group['endpoints'] as $endpoint)
                                @php
                                    $methodBadge = match ($endpoint['method']) {
                                        'GET' => 'badge-info',
                                        'POST' => 'badge-success',
                                        'PUT' => 'badge-warning',
                                        'DELETE' => 'badge-danger',
                                        default => 'badge-neutral',
                                    };
                                @endphp
                                <div class="rounded-lg border border-neutral-200 p-3.5 dark:border-neutral-800">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="badge {{ $methodBadge }}">{{ $endpoint['method'] }}</span>
                                        <span class="font-mono text-sm text-neutral-700 dark:text-neutral-200">{{ $endpoint['path'] }}</span>
                                        @if (! empty($endpoint['auth']))
                                            <span class="badge badge-primary">Session auth required</span>
                                        @endif
                                    </div>
                                    <p class="mt-2 text-sm text-neutral-700 dark:text-neutral-200">{{ $endpoint['description'] }}</p>
                                    <p class="mt-1.5 text-xs text-neutral-500 dark:text-neutral-400"><span class="font-medium text-neutral-600 dark:text-neutral-300">Params:</span> {{ $endpoint['params'] }}</p>
                                    <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400"><span class="font-medium text-neutral-600 dark:text-neutral-300">Response:</span> {{ $endpoint['response'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- User Guide --}}
            <div x-show="tab === 'guide'" x-cloak>
                <div class="card p-5 sm:p-6 mb-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">User Guide</h3>
                            <p class="mt-1 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                                A practical guide for administrators and managers: signing in, the dashboard,
                                attendance, reports, employee and location management, and the scan kiosk.
                            </p>
                        </div>
                        <a href="{{ route('admin.settings.user-guide.pdf') }}" class="{{ $downloadButton }}">
                            <svg class="{{ $downloadIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 8h12a2 2 0 002-2V8a2 2 0 00-2-2h-4l-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Download PDF
                        </a>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    @include('admin.settings.partials.user-guide-content')
                </div>
            </div>

            {{-- Technical Docs --}}
            <div x-show="tab === 'tech'" x-cloak>
                <div class="card p-5 sm:p-6 mb-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Technical Documentation</h3>
                            <p class="mt-1 text-sm leading-relaxed text-neutral-500 dark:text-neutral-400">
                                Architecture, database schema, authentication flows, PDF pipeline, deployment
                                notes and security controls for developers and IT operations.
                            </p>
                        </div>
                        <a href="{{ route('admin.settings.technical-docs.pdf') }}" class="{{ $downloadButton }}">
                            <svg class="{{ $downloadIcon }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m-9 8h12a2 2 0 002-2V8a2 2 0 00-2-2h-4l-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Download PDF
                        </a>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    @include('admin.settings.partials.technical-docs-content')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
