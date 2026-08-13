<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact Us — ASYCUDA Support Team</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const theme = stored || 'dark';
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
</head>
<body class="font-sans text-neutral-800 antialiased" x-data>
    <div class="relative flex min-h-screen flex-col overflow-hidden bg-gradient-to-br from-white via-neutral-50 to-primary-50 dark:from-neutral-950 dark:via-neutral-900 dark:to-primary-950">
        <div class="pointer-events-none absolute inset-0 opacity-[0.14] [background-image:radial-gradient(circle_at_15%_20%,rgb(139,111,247),transparent_38%),radial-gradient(circle_at_85%_0%,rgb(21,188,224),transparent_32%)]"></div>

        <nav class="fixed inset-x-0 top-0 z-40 border-b border-neutral-200/70 bg-white/70 backdrop-blur-sm dark:border-white/10 dark:bg-neutral-950/50">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2.5 focus:outline-none">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-800 shadow-card">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <span class="hidden truncate font-semibold tracking-tight text-neutral-900 min-[420px]:inline dark:text-white">Smart Attendance</span>
                </a>

                <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                    <button @click="toggleTheme()" title="Toggle theme" class="rounded-full p-2 text-neutral-500 transition duration-150 ease-in-out hover:bg-neutral-100 hover:text-neutral-900 focus:outline-none dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white">
                        <svg class="block h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-ghost whitespace-nowrap border border-neutral-300 bg-white text-neutral-700 hover:bg-neutral-50 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost whitespace-nowrap border border-neutral-300 bg-white px-2.5 py-1.5 text-xs text-neutral-700 hover:bg-neutral-50 sm:px-3 sm:py-2 sm:text-sm dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <span class="sm:hidden">Login</span>
                            <span class="hidden sm:inline">Admin / Manager login</span>
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <div class="relative flex w-full flex-1 flex-col items-center justify-center px-4 pt-20 pb-10 text-center">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-primary-500 to-primary-800 shadow-lift">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>

            <h1 class="max-w-2xl text-3xl font-bold tracking-tight text-neutral-900 dark:text-white sm:text-4xl">Contact ASYCUDA Support Team</h1>
            <p class="mx-auto mt-3 max-w-xl text-neutral-500 dark:text-neutral-300">
                If you experience any issues or require assistance with the ASYCUDA system, please contact the support team using the details below.
            </p>
        </div>

        <div class="relative mx-auto w-full max-w-3xl px-4 pb-16 sm:px-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <a href="mailto:awhelpdesk@customs.gov.sb" class="card group flex flex-col items-center p-6 text-center transition-transform duration-200 hover:-translate-y-0.5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-primary-700 shadow-card transition-transform duration-200 group-hover:scale-105">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <span class="mt-3 text-2xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Email</span>
                    <span class="mt-1 break-all text-sm font-semibold text-neutral-900 group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">awhelpdesk@customs.gov.sb</span>
                </a>

                <a href="tel:24377" class="card group flex flex-col items-center p-6 text-center transition-transform duration-200 hover:-translate-y-0.5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-secondary-500 to-secondary-700 shadow-card transition-transform duration-200 group-hover:scale-105">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                    </span>
                    <span class="mt-3 text-2xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Phone</span>
                    <span class="mt-1 text-2xl font-bold tracking-tight text-neutral-900 group-hover:text-secondary-600 dark:text-white dark:group-hover:text-secondary-400">24377</span>
                </a>

                <a href="tel:20038" class="card group flex flex-col items-center p-6 text-center transition-transform duration-200 hover:-translate-y-0.5">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-info-500 to-info-700 shadow-card transition-transform duration-200 group-hover:scale-105">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                    </span>
                    <span class="mt-3 text-2xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">Alternative Phone</span>
                    <span class="mt-1 text-2xl font-bold tracking-tight text-neutral-900 group-hover:text-info-600 dark:text-white dark:group-hover:text-info-400">20038</span>
                </a>
            </div>

            <div class="mt-8">
                <div class="card flex items-start gap-4 border-info-300/70 bg-info-50 p-6 dark:border-info-700/40 dark:bg-info-500/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-info-100 text-info-700 dark:bg-info-500/20 dark:text-info-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </span>
                    <div class="min-w-0 text-left">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Support Notice</h2>
                        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-300">
                            For any ASYCUDA system issues, account access problems, technical errors, or support requests, please contact the ASYCUDA Team using the contact details above.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
