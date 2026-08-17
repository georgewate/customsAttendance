@php
    $employee = Auth::guard('employee')->user();
    $navSections = [
        'Overview' => [
            [
                'label' => 'Dashboard',
                'route' => route('employee.dashboard'),
                'pattern' => 'employee.dashboard',
            ],
        ],
        'Attendance' => [
            [
                'label' => 'Clock In / Out',
                'route' => $employee?->homeLocation ? route('scan.show', $employee->homeLocation->qr_token) : null,
                'pattern' => 'scan.*',
            ],
            [
                'label' => 'Attendance History',
                'route' => route('employee.attendance'),
                'pattern' => 'employee.attendance',
            ],
        ],
        'Account' => [
            [
                'label' => 'Update PIN',
                'route' => route('employee.pin'),
                'pattern' => 'employee.pin*',
            ],
            [
                'label' => 'Update Profile',
                'route' => route('employee.profile'),
                'pattern' => 'employee.profile*',
            ],
        ],
    ];

    $navSections = array_map(
        fn ($items) => array_values(array_filter($items, fn ($item) => ! empty($item['route']))),
        $navSections
    );
    $navSections = array_filter($navSections, fn ($items) => ! empty($items));
@endphp

<nav class="sticky top-0 z-40 border-b border-neutral-200/70 bg-white/80 backdrop-blur dark:border-white/10 dark:bg-neutral-950/80">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button @click="open = ! open" aria-label="Toggle menu"
                class="inline-flex items-center justify-center rounded-md p-2 text-neutral-500 transition duration-150 ease-in-out hover:bg-neutral-100 hover:text-neutral-900 lg:hidden dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white focus:outline-none">
                <svg :class="{'hidden': open, 'inline-flex': ! open}" class="inline-flex h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg :class="{'hidden': ! open, 'inline-flex': open}" class="hidden h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-2.5 focus:outline-none">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 shadow-card">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="font-semibold tracking-tight text-neutral-900 dark:text-white">Smart Attendance</span>
            </a>
        </div>

        <div class="hidden items-center gap-1 lg:flex">
            @foreach ($navSections as $sectionName => $items)
                @if (count($items) === 1 && $sectionName === 'Overview')
                    <a href="{{ $items[0]['route'] }}"
                        class="rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs($items[0]['pattern']) ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400' : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                        {{ $items[0]['label'] }}
                    </a>
                @else
                    <div class="relative" x-data="{ sectionOpen: false }" @click.outside="sectionOpen = false">
                        <button type="button"
                            @click="sectionOpen = ! sectionOpen"
                            :aria-expanded="sectionOpen.toString()"
                            class="flex items-center gap-1 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ collect($items)->contains(fn ($item) => request()->routeIs($item['pattern'])) ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400' : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                            <span>{{ $sectionName }}</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="sectionOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="sectionOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 top-full z-50 mt-2 w-56 space-y-1 rounded-control border border-neutral-200 bg-white p-2 shadow-soft dark:border-white/10 dark:bg-neutral-900"
                             style="display: none;">
                            @foreach ($items as $item)
                                <a href="{{ $item['route'] }}"
                                    class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs($item['pattern']) ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400' : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white' }}">
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-60"></span>
                                    <span class="flex-1">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            <button @click="toggleTheme()" title="Toggle theme"
                class="rounded-full p-2 text-neutral-500 transition duration-150 ease-in-out hover:bg-neutral-100 hover:text-neutral-900 focus:outline-none dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white">
                <svg class="block h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            <a href="{{ route('employee.profile') }}" class="hidden items-center gap-2.5 rounded-control border border-neutral-200 bg-white px-2.5 py-1.5 transition-colors duration-150 hover:border-neutral-300 sm:flex dark:border-white/10 dark:bg-white/5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                    @if ($employee?->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="" class="h-full w-full object-cover">
                    @else
                        <svg class="h-5 w-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    @endif
                </span>
                <span class="hidden max-w-[10rem] truncate text-sm font-medium text-neutral-700 md:block dark:text-neutral-200">{{ $employee?->name }}</span>
            </a>

            <form method="POST" action="{{ route('employee.logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-neutral-200/70 lg:hidden dark:border-white/10">
        <div class="space-y-4 px-4 py-3">
            @foreach ($navSections as $sectionName => $items)
                <div>
                    <p class="px-3 pb-1 text-2xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">{{ $sectionName }}</p>
                    <div class="space-y-1">
                        @foreach ($items as $item)
                            <a href="{{ $item['route'] }}"
                                class="block rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs($item['pattern']) ? 'bg-primary-500/10 text-primary-600 dark:text-primary-400' : 'text-neutral-600 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-white/10' }}">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</nav>
