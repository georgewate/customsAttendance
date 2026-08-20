<nav x-data="{ open: false }">
    {{-- Desktop top navbar --}}
    <header class="sticky top-0 z-40 hidden border-b border-white/10 bg-neutral-950/90 backdrop-blur lg:block">
        <div class="flex h-16 items-center gap-2 px-4">
            <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center gap-2.5 pr-2 focus:outline-none">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-700 shadow-card">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="truncate font-semibold tracking-tight text-white">Smart Attendance</span>
            </a>

            <div class="mx-2 h-6 w-px shrink-0 bg-white/10"></div>

            {{-- Top-level links --}}
            <div class="flex min-w-0 items-center gap-1">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" label="Dashboard">
                    <span>{{ __('Dashboard') }}</span>
                </x-nav-link>

                {{-- Attendance dropdown --}}
                <div class="relative" x-data="{ attendanceOpen: @js(request()->routeIs('admin.attendance.index', 'admin.attendance.reports*', 'admin.attendance.export')) }" @click.outside="attendanceOpen = false">
                    <button type="button"
                            @click="attendanceOpen = ! attendanceOpen"
                            :aria-expanded="attendanceOpen.toString()"
                            class="nav-link w-full text-start {{ request()->routeIs('admin.attendance.index', 'admin.attendance.reports*', 'admin.attendance.export') ? 'nav-link-active' : 'nav-link-idle' }}">
                        <span>{{ __('Attendance') }}</span>
                        <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="attendanceOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="attendanceOpen"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute left-0 top-full z-50 mt-2 w-52 space-y-1 rounded-control border border-white/10 bg-neutral-900 p-2 shadow-soft"
                         style="display: none;">
                        <a href="{{ route('admin.attendance.index') }}"
                           class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.attendance.index', 'admin.attendance.export') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-60"></span>
                            <span class="flex-1">{{ __('Attendance Log') }}</span>
                        </a>
                        <a href="{{ route('admin.attendance.reports') }}"
                           class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.attendance.reports*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-current opacity-60"></span>
                            <span class="flex-1">{{ __('Reports') }}</span>
                        </a>
                    </div>
                </div>

                <x-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')" label="Employees">
                    <span>{{ __('Employees') }}</span>
                </x-nav-link>

                @if (Auth::user()->isAdmin())
                    {{-- Administration dropdown --}}
                    <div class="relative" x-data="{ adminOpen: false }" @click.outside="adminOpen = false">
                        <button type="button"
                                @click="adminOpen = ! adminOpen"
                                :aria-expanded="adminOpen.toString()"
                                class="nav-link w-full text-start nav-link-idle">
                            <span>{{ __('Administration') }}</span>
                            <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="adminOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="adminOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full z-50 mt-2 w-60 space-y-1 rounded-control border border-white/10 bg-neutral-900 p-2 shadow-soft"
                             style="display: none;">
                            <a href="{{ route('admin.attendance-reasons.index') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.attendance-reasons.*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="flex-1">{{ __('Attendance Reasons') }}</span>
                            </a>
                            <a href="{{ route('admin.attendance.settings') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.attendance.settings*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="flex-1">{{ __('Attendance Settings') }}</span>
                            </a>
                            <a href="{{ route('admin.locations.index') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.locations.*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="flex-1">{{ __('Locations') }}</span>
                            </a>
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="flex-1">{{ __('Users') }}</span>
                            </a>
                            <a href="{{ route('admin.settings.index') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.settings.*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="flex-1">{{ __('Settings') }}</span>
                            </a>
                            <a href="{{ route('admin.audit-logs.index') }}"
                               class="flex items-center gap-2 rounded-control px-3 py-2 text-sm font-medium transition-colors duration-150 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary-500/15 text-white' : 'text-neutral-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="flex-1">{{ __('Audit Log') }}</span>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right side --}}
            <div class="ml-auto flex shrink-0 items-center gap-2">
                <button @click="toggleTheme()" title="Toggle theme" class="inline-flex h-9 w-9 items-center justify-center rounded-control p-2 text-neutral-400 transition-colors duration-150 hover:bg-white/10 hover:text-white focus:outline-none">
                    <svg class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>

                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 rounded-control border border-white/10 bg-white/5 px-3 py-2 text-left transition-colors duration-150 hover:bg-white/10 focus:outline-none">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-xs font-bold text-white">
                                @if (Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-white">{{ Auth::user()->name }}</span>
                                <span class="block truncate text-xs text-neutral-400">{{ Auth::user()->email }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </header>

    {{-- Mobile header --}}
    <div class="sticky top-0 z-50 flex h-16 items-center justify-between border-b border-white/10 bg-neutral-950/90 px-4 backdrop-blur lg:hidden">
        <div class="flex items-center gap-2">
            <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-neutral-300 transition duration-150 ease-in-out hover:bg-white/10 hover:text-white focus:outline-none">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 focus:outline-none">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-primary-500 to-primary-700">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <span class="font-semibold tracking-tight text-white">Smart Attendance</span>
            </a>
        </div>

        <button @click="toggleTheme()" title="Toggle theme" class="inline-flex items-center justify-center rounded-full p-2 text-neutral-300 transition duration-150 ease-in-out hover:bg-white/10 hover:text-white focus:outline-none">
            <svg class="h-5 w-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            <svg class="h-5 w-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-b border-white/10 bg-neutral-950/95 lg:hidden">
        <div class="space-y-1 pt-2 pb-3">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.attendance.index')" :active="request()->routeIs('admin.attendance.index', 'admin.attendance.export')">
                {{ __('Attendance') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.attendance.reports')" :active="request()->routeIs('admin.attendance.reports*')">
                {{ __('Reports') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                {{ __('Employees') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.attendance-reasons.index')" :active="request()->routeIs('admin.attendance-reasons.*')">
                    {{ __('Attendance Reasons') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.attendance.settings')" :active="request()->routeIs('admin.attendance.settings*')">
                    {{ __('Attendance Settings') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">
                    {{ __('Locations') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                    {{ __('Settings') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                    {{ __('Audit Log') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <div class="border-t border-white/10 pt-4 pb-3">
            <div class="px-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">
                        @if (Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </span>
                    <div class="min-w-0">
                        <div class="truncate text-base font-medium text-white">{{ Auth::user()->name }}</div>
                        <div class="truncate text-sm font-medium text-neutral-400">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
