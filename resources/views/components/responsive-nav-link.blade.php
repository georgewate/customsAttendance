@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-s-4 border-primary-400 bg-white/10 px-4 py-2.5 text-start text-sm font-medium text-white transition duration-150 ease-in-out'
            : 'block w-full border-s-4 border-transparent px-4 py-2.5 text-start text-sm font-medium text-neutral-200 hover:border-primary-400/50 hover:bg-white/5 hover:text-white transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
