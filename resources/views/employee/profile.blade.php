<x-employee-layout>
    <div class="mx-auto max-w-xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-white">Update Profile</h1>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Upload a new profile picture. Supported formats: JPG, JPEG, PNG (max 2 MB).</p>
        </div>

        <div class="card p-6" x-data="{ preview: @js($employee->photo_url ?? null) }">
            <form method="POST" action="{{ route('employee.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="label">Current picture</label>
                    <div class="mt-2 flex items-center gap-5">
                        <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-4 ring-primary-500/15 dark:bg-neutral-800">
                            <img x-show="preview" :src="preview" alt="Profile preview" class="h-full w-full object-cover">
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
                                <input id="photo" name="photo" type="file" accept="image/jpeg,image/png" required
                                    class="sr-only"
                                    @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
                            </label>
                            <p class="mt-2 text-xs text-neutral-500 dark:text-neutral-400">Select a new image and preview it before saving.</p>
                        </div>
                    </div>
                    @error('photo')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn btn-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                        </svg>
                        Save Picture
                    </button>
                    <a href="{{ route('employee.dashboard') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-employee-layout>
