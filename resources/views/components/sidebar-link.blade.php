@props(['active' => false])

@php
    $classes = $active
        ? 'flex items-center rounded-xl bg-blue-600 px-3 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-950/20 ring-1 ring-inset ring-blue-300/25'
        : 'flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-white/[0.07] hover:text-white focus:bg-white/[0.07] focus:text-white focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
