@php
/**
 * @var string $name
 * @var string $type text|email|password
 * @var string $placeholder
 * @var bool   $error
 */
$type ??= 'text';
$placeholder ??= '';
$error ??= false;
$ring = $error
    ? 'ring-1 ring-inset ring-red-400 focus:ring-red-500'
    : 'ring-1 ring-inset ring-black/10 focus:ring-brand-teal';
@endphp
<input
    type="{{ $type }}"
    name="{{ $name }}"
    placeholder="{{ $placeholder }}"
    class="w-full rounded-md px-3 py-2 text-sm outline-none transition disabled:cursor-not-allowed disabled:bg-black/5 {{ $ring }}"
>
