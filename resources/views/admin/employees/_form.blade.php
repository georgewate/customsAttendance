@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="employee_code" value="Employee code" />
        <x-text-input id="employee_code" name="employee_code" class="mt-1.5 block w-full" value="{{ old('employee_code', $employee->employee_code ?? '') }}" required />
        <x-input-error :messages="$errors->get('employee_code')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1.5 block w-full" value="{{ old('name', $employee->name ?? '') }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div x-data="{ preview: @js($employee->photo_url ?? null) }">
        <x-input-label for="photo" value="Profile picture" />
        <div class="mt-1.5 flex items-center gap-4">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-neutral-100 ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-neutral-700">
                <img x-show="preview" :src="preview" alt="" class="h-full w-full object-cover">
                <span x-show="!preview" class="text-neutral-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
            </span>
            <label class="cursor-pointer">
                <span class="btn-ghost">Choose image</span>
                <input id="photo" name="photo" type="file" accept="image/*" class="sr-only"
                    @change="const f = $event.target.files[0]; if (f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
            </label>
        </div>
        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email (optional)" />
        <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" value="{{ old('email', $employee->email ?? '') }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="home_location_id" value="Location" />
        <select id="home_location_id" name="home_location_id" class="input mt-1.5" required>
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(old('home_location_id', $employee->home_location_id ?? null) == $location->id)>
                    {{ $location->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('home_location_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="pin" :value="isset($employee) ? 'Reset PIN (leave blank to keep current)' : 'PIN'" />
        <x-text-input id="pin" name="pin" type="text" inputmode="numeric" class="mt-1.5 block w-full" autocomplete="off" :required="! isset($employee)" />
        <x-input-error :messages="$errors->get('pin')" class="mt-2" />
    </div>

    @isset($employee)
        <div class="flex items-center">
            <input id="active" name="active" type="checkbox" value="1" {{ old('active', $employee->active) ? 'checked' : '' }}
                class="checkbox">
            <label for="active" class="ms-2 text-sm text-neutral-600 dark:text-neutral-300">Active</label>
        </div>
    @endisset

    <div class="flex items-center gap-3 pt-2">
        <x-primary-button>Save</x-primary-button>
        <a href="{{ route('admin.employees.index') }}" class="btn-ghost self-center">Cancel</a>
    </div>
</div>
