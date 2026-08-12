@php
    $reopenToken = old('qr_token');
    $reopenLocation = $reopenToken ? $locations->firstWhere('qr_token', $reopenToken) : null;
    $hasLoginError = $errors->has('employee_code') || $errors->has('pin');
    $initOpen = $reopenLocation && $hasLoginError ? 'true' : 'false';
    $initToken = json_encode($reopenToken ?? '');
    $initName = json_encode($reopenLocation?->name ?? '');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.location-qr svg{width:108px;height:108px;display:block;margin:0 auto}</style>
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const theme = stored || 'dark';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
</head>
<body class="font-sans text-neutral-800 antialiased">
    <div x-data="{
            open: {{ $initOpen }},
            token: {{ $initToken }},
            name: {{ $initName }},
            openLocation(token, name) { this.token = token; this.name = name; this.open = true; },
            close() { this.open = false; }
        }"
        class="relative flex min-h-screen flex-col overflow-hidden bg-gradient-to-br from-white via-neutral-50 to-primary-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950">
        <div class="pointer-events-none absolute inset-0 opacity-[0.14] [background-image:radial-gradient(circle_at_15%_20%,rgb(139,111,247),transparent_38%),radial-gradient(circle_at_85%_0%,rgb(21,188,224),transparent_32%)]"></div>

        <nav class="sticky top-0 z-20 border-b border-neutral-200/70 bg-white/70 backdrop-blur-sm dark:border-white/10 dark:bg-neutral-950/50">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex items-center gap-2.5 focus:outline-none">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-800 shadow-card">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="font-semibold tracking-tight text-neutral-900 dark:text-white">Smart Attendance</span>
                </a>

                <div class="flex items-center gap-2">
                    <button @click="toggleTheme()" title="Toggle theme" class="rounded-full p-2 text-neutral-500 transition duration-150 ease-in-out hover:bg-neutral-100 hover:text-neutral-900 focus:outline-none dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white">
                        <svg class="block h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-ghost border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            Admin / Manager login
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <div class="relative flex w-full flex-1 flex-col items-center justify-center px-4 py-10 text-center">
            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-800 shadow-lift">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <h1 class="mb-1 text-2xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-3xl">Smart Attendance</h1>
            <p class="mb-7 max-w-sm text-neutral-500 dark:text-neutral-300">Choose your location and sign in with your employee ID and PIN.</p>

            @if (session('success'))
                <div class="alert alert-success mb-7 w-full max-w-md mx-auto flex items-center gap-2 text-left">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($locations->isNotEmpty())
                <div class="mb-7 w-full max-w-3xl">
                    <div class="flex flex-wrap justify-center gap-4">
                        @foreach ($locations as $location)
                            <button type="button"
                                @click="openLocation(@js($location->qr_token), @js($location->name))"
                                class="group flex w-full max-w-xs flex-col items-center rounded-2xl border border-neutral-200 bg-white p-4 text-center shadow-card backdrop-blur-sm transition-all duration-200 hover:border-primary-300 hover:shadow-soft focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-white dark:shadow-none dark:hover:border-primary-400/50 dark:hover:bg-white/10 sm:w-64">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-800 shadow-card transition-transform duration-200 group-hover:scale-105">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                <h2 class="mt-2.5 text-base font-semibold text-neutral-900 dark:text-white">{{ $location->name }}</h2>
                                @if ($location->address)
                                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-300/80">{{ $location->address }}</p>
                                @endif
                                <span class="location-qr mt-3 rounded-xl border border-neutral-200 bg-white p-2 shadow-inner dark:border-white/15">
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(200)->generate($location->scanUrl()) !!}
                                </span>
                                <span class="mt-2 inline-flex items-center gap-1 text-xs text-neutral-500 dark:text-neutral-300/80">
                                    Tap to sign in
                                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @else
                <p class="mb-7 text-neutral-500 dark:text-neutral-300">No locations are registered yet.</p>
            @endif
        </div>

        <div x-cloak x-show="open" x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-secondary-950/70 p-4 backdrop-blur-sm"
            @click="close()">
        <div class="w-full max-w-sm rounded-card bg-white p-6 shadow-lift dark:border dark:border-white/10 dark:bg-neutral-900/90 dark:shadow-black/40" @click.stop>
            <div class="mb-1 flex items-start justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-800">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-white" x-text="name"></h2>
                </div>
                <button type="button" @click="close()" aria-label="Close"
                    class="rounded p-1 text-neutral-400 transition-colors hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="mb-5 text-sm text-neutral-500 dark:text-neutral-400">Enter your employee ID and PIN to clock in or out.</p>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" :action="'/scan/' + token + '/login'" class="space-y-4">
                @csrf
                <input type="hidden" name="qr_token" :value="token">

                <div>
                    <label for="employee_code" class="label">Employee ID</label>
                    <input id="employee_code" name="employee_code" type="text" value="{{ old('employee_code') }}"
                        inputmode="numeric" autocomplete="off" autofocus required
                        class="input mt-1.5" />
                </div>

                <div>
                    <label for="pin" class="label">PIN</label>
                    <input id="pin" name="pin" type="password" inputmode="numeric" autocomplete="off" required
                        class="input mt-1.5" />
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-base">
                    Continue
                </button>
            </form>
            </div>
        </div>
    </div>
</body>
</html>
