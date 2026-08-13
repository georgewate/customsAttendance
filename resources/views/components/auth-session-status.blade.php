@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'alert alert-success flex items-start gap-2 font-medium']) }}>
        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $status }}
    </div>
@endif
