@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1.5 block w-full" value="{{ old('name', $user->name ?? '') }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" value="{{ old('email', $user->email ?? '') }}" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" :value="isset($user) ? 'Reset password (leave blank to keep current)' : 'Password'" />
        <x-text-input id="password" name="password" type="password" class="mt-1.5 block w-full" autocomplete="new-password" :required="! isset($user)" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" :value="isset($user) ? 'Confirm new password' : 'Confirm password'" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" autocomplete="new-password" :required="! isset($user)" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="role" value="Role" />
        <select id="role" name="role" class="input mt-1.5" required onchange="document.getElementById('location-field').classList.toggle('hidden', this.value !== 'manager')">
            <option value="manager" @selected(old('role', $user->role ?? 'manager') === 'manager')>Manager (single location)</option>
            <option value="admin" @selected(old('role', $user->role ?? 'manager') === 'admin')>Admin (all locations)</option>
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>

    <div id="location-field" class="{{ old('role', $user->role ?? 'manager') === 'manager' ? '' : 'hidden' }}">
        <x-input-label for="location_id" value="Location" />
        <select id="location_id" name="location_id" class="input mt-1.5">
            @foreach ($locations as $location)
                <option value="{{ $location->id }}" @selected(old('location_id', $user->location_id ?? null) == $location->id)>{{ $location->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('location_id')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3 pt-2">
        <x-primary-button>Save</x-primary-button>
        <a href="{{ route('admin.users.index') }}" class="btn-ghost self-center">Cancel</a>
    </div>
</div>
