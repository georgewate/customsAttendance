<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $location->name }} — Attendance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-neutral-800 antialiased">
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-secondary-950 via-secondary-900 to-primary-950 px-4 py-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.06] [background-image:radial-gradient(circle_at_15%_20%,white,transparent_38%),radial-gradient(circle_at_85%_0%,white,transparent_32%)]"></div>

        <div class="relative w-full max-w-sm rounded-card bg-white p-6 shadow-lift dark:bg-neutral-900">
            <div class="flex flex-col items-center text-center">
                <span class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-4 ring-primary-500/15 dark:bg-neutral-800">
                    @if ($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="{{ $employee->name }}" class="h-full w-full object-cover">
                    @else
                        <svg class="h-10 w-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    @endif
                </span>
                <h1 class="mt-3 text-xl font-semibold text-neutral-900 dark:text-white">{{ $employee->name }}</h1>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">{{ $employee->employee_code }} &middot; {{ $location->name }}</p>
                <p class="mt-4 mb-6 text-sm">
                    @if ($latest?->type === 'time_in')
                        <span class="badge badge-success">
                            <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                            Clocked in since {{ $latest->scanned_at->format('g:i A') }}
                        </span>
                    @else
                        <span class="badge badge-neutral">Not clocked in today.</span>
                    @endif
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-3">
                <form method="POST" action="{{ route('scan.time-in', $location->qr_token) }}">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-control py-4 text-lg font-semibold text-white transition-colors duration-150 {{ $latest?->type === 'time_in' ? 'cursor-not-allowed bg-success-300' : 'bg-success-600 hover:bg-success-500' }}"
                        @disabled($latest?->type === 'time_in')>
                        Time In
                    </button>
                </form>

                <form method="POST" action="{{ route('scan.time-out', $location->qr_token) }}">
                    @csrf
                    <button type="submit"
                        class="w-full rounded-control py-4 text-lg font-semibold text-white transition-colors duration-150 {{ $latest?->type !== 'time_in' ? 'cursor-not-allowed bg-danger-300' : 'bg-danger-600 hover:bg-danger-500' }}"
                        @disabled($latest?->type !== 'time_in')>
                        Time Out
                    </button>
                </form>
            </div>

            <form method="POST" action="{{ route('scan.logout', $location->qr_token) }}" class="mt-6 text-center">
                @csrf
                <button type="submit" class="btn-link">Not you? Sign out</button>
            </form>
        </div>
    </div>
</body>
</html>
