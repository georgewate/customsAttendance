<section>
    <header>
        <h2 class="text-lg font-medium text-neutral-900 dark:text-white">
            {{ __('Profile Picture') }}
        </h2>

        <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
            {{ __('Upload a profile picture. Supported formats: JPG, JPEG, PNG, GIF, WEBP (max 2 MB).') }}
        </p>
    </header>

    <div class="mt-6" x-data="{ preview: @js($user->avatar_url ?? null) }">
        <form method="post" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="flex items-center gap-5">
                <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-4 ring-primary-500/15 dark:bg-neutral-800">
                    <img x-show="preview" :src="preview" alt="{{ $user->name }}" class="h-full w-full object-cover">
                    <span x-show="!preview" class="text-neutral-400">
                        <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                </span>

                <div>
                    <label class="cursor-pointer">
                        <span class="btn btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                            </svg>
                            Choose image
                        </span>
                        <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                            class="sr-only"
                            @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
                    </label>
                    <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">Select a new image and preview it before saving.</p>
                </div>
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Save Picture') }}</x-primary-button>

                @if (session('status') === 'avatar-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-neutral-600 dark:text-neutral-400"
                    >{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>

        @if ($user->avatar)
            <form method="post" action="{{ route('profile.avatar.destroy') }}" class="mt-4">
                @csrf
                @method('delete')

                <button type="submit" class="btn-link text-danger-600 hover:text-danger-500 dark:text-danger-400">
                    {{ __('Remove picture') }}
                </button>
            </form>
        @endif
    </div>
</section>
