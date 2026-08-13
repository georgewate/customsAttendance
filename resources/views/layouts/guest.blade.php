<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const stored = localStorage.getItem('theme');
                const theme = stored || 'dark';
                document.documentElement.classList.toggle('dark', theme === 'dark');
            })();
        </script>
    </head>
    <body class="font-sans text-neutral-800 antialiased dark:text-neutral-200" x-data>
        <div class="fixed top-4 right-4 z-50">
            <button @click="toggleTheme()" title="Toggle theme" class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white/10 text-white hover:bg-white/20 focus:outline-none transition duration-150 ease-in-out">
                <svg class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>
        </div>
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-neutral-950 via-neutral-900 to-primary-950 px-4 py-10 sm:py-12">
            <div class="pointer-events-none absolute inset-0 opacity-[0.14] [background-image:radial-gradient(circle_at_15%_20%,rgb(139,111,247),transparent_40%),radial-gradient(circle_at_85%_0%,rgb(21,188,224),transparent_36%)]"></div>

            <div class="relative flex items-center gap-3">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-800 shadow-card">
                    <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="text-xl font-semibold tracking-tight text-white">Smart Attendance</span>
            </div>

            <div class="relative mt-6 w-full max-w-md animate-fade-in-up rounded-card border border-neutral-200/50 bg-white p-6 shadow-lift dark:border-white/10 dark:bg-neutral-900/80 dark:shadow-black/40 sm:p-8">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
