<div class="space-y-5">
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <div>
            <x-input-label for="category" value="Reason Category" />
            <select id="category" name="category" class="input mt-1.5 !w-full" required>
                <option value="">Select a category</option>
                @foreach (\App\Models\AttendanceReason::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" @selected(old('category', $reason->category ?? '') == $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="name" value="Reason Name" />
            <x-text-input id="name" name="name" class="mt-1.5 block w-full" value="{{ old('name', $reason->name ?? '') }}"
                placeholder="e.g. Medical Appointment" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="input mt-1.5 !w-full"
            placeholder="Optional short description of when this reason applies.">{{ old('description', $reason->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <label class="flex cursor-pointer items-center gap-3">
            <input id="active" name="active" type="checkbox" value="1" class="checkbox"
                {{ old('active', $reason->active ?? true) ? 'checked' : '' }}>
            <span class="text-sm text-neutral-600 dark:text-neutral-300">Active</span>
        </label>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">Inactive reasons are hidden from the employee clock-in / clock-out form.</p>
    </div>
</div>
