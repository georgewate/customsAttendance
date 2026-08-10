<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $location->name }} — Sign in</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-neutral-800 antialiased">
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-neutral-950 via-neutral-900 to-primary-950 px-4 py-8">
        <div class="pointer-events-none absolute inset-0 opacity-[0.14] [background-image:radial-gradient(circle_at_15%_20%,rgb(139,111,247),transparent_40%),radial-gradient(circle_at_85%_0%,rgb(21,188,224),transparent_36%)]"></div>

        <div class="relative w-full max-w-sm rounded-card bg-white p-6 shadow-lift dark:border dark:border-white/10 dark:bg-neutral-900/80 dark:shadow-black/40">
            <div class="mb-4 flex items-center gap-2.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-800">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <h1 class="text-lg font-semibold text-neutral-900 dark:text-white">{{ $location->name }}</h1>
            </div>
            <p class="mb-6 text-sm text-neutral-500 dark:text-neutral-400">Enter your employee code and PIN to clock in or out.</p>

            @if ($errors->any())
                <div class="alert alert-danger mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('scan.login', $location->qr_token) }}" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="employee_code" value="Employee code" />
                    <x-text-input id="employee_code" name="employee_code" type="text"
                        inputmode="text" autofocus autocomplete="off"
                        class="mt-1.5 block w-full text-lg" required />
                </div>

                <div>
                    <x-input-label for="pin" value="PIN" />
                    <x-text-input id="pin" name="pin" type="password"
                        inputmode="numeric" autocomplete="off"
                        class="mt-1.5 block w-full text-lg" required />
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-lg">
                    Continue
                </button>
            </form>
        </div>
    </div>
</body>
</html>
