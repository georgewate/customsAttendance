@csrf

<div class="space-y-5">
    <div>
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" name="name" class="mt-1.5 block w-full" value="{{ old('name', $location->name ?? '') }}" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" name="address" class="mt-1.5 block w-full" value="{{ old('address', $location->address ?? '') }}" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="timezone" value="Timezone" />
        <x-text-input id="timezone" name="timezone" class="mt-1.5 block w-full" value="{{ old('timezone', $location->timezone ?? 'UTC') }}" required />
        <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
    </div>

    @isset($location)
        <div class="flex items-center">
            <input id="active" name="active" type="checkbox" value="1" {{ old('active', $location->active) ? 'checked' : '' }}
                class="checkbox">
            <label for="active" class="ms-2 text-sm text-neutral-600 dark:text-neutral-300">Active</label>
        </div>
    @else
        <input type="hidden" name="active" value="1">
    @endisset

    <div class="flex items-center gap-3 pt-2">
        <x-primary-button>Save</x-primary-button>
        <a href="{{ route('admin.locations.index') }}" class="btn-ghost self-center">Cancel</a>
    </div>
</div>
