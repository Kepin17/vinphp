@props(string $label, string $href = '#', string $variant = 'primary', string $type = '')
@php
$classes = $variant === 'primary'
    ? 'bg-brand-orange text-black hover:bg-brand-orange/90'
    : 'bg-white text-brand-teal ring-1 ring-inset ring-brand-teal/30 hover:bg-brand-teal/5';
$classes = 'inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold shadow-sm transition ' . $classes;
@endphp
@if($type !== '')
    <button type="{{ $type }}" class="{{ $classes }}">{{ $label }}</button>
@else
    <a href="{{ $href }}" class="{{ $classes }}">{{ $label }}</a>
@endif
