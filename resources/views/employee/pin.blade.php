<x-employee-layout>
    <div class="mx-auto max-w-xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Update PIN</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Your PIN is used to clock in and out. Choose a numeric PIN between 4 and 8 digits.</p>
        </div>

        <div class="card p-6">
            <form method="POST" action="{{ route('employee.pin.update') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="current_pin" class="label">Current PIN</label>
                    <input id="current_pin" name="current_pin" type="password" inputmode="numeric" autocomplete="current-password" required autofocus
                        class="input mt-1.5" />
                    @error('current_pin')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_pin" class="label">New PIN</label>
                    <input id="new_pin" name="new_pin" type="password" inputmode="numeric" autocomplete="new-password" required
                        class="input mt-1.5" />
                    @error('new_pin')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_pin_confirmation" class="label">Confirm new PIN</label>
                    <input id="new_pin_confirmation" name="new_pin_confirmation" type="password" inputmode="numeric" autocomplete="new-password" required
                        class="input mt-1.5" />
                    @error('new_pin_confirmation')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Update PIN
                    </button>
                    <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-employee-layout>
