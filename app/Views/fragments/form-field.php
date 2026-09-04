@php
/**
 * @var string $label
 * @var string $name
 * @var string $type
 * @var string $placeholder
 * @var bool   $error
 * @var string $hint  Optional helper/error text below the field.
 */
$hint ??= '';
$error ??= false;
@endphp
<label class="block text-left">
    <span class="text-sm font-medium text-black">{{ $label }}</span>
    <div class="mt-1">
        @Input([
            'name' => $name,
            'type' => $type ?? 'text',
            'placeholder' => $placeholder ?? '',
            'error' => $error,
        ])
    </div>
    @if($hint !== '')
        <p class="mt-1 text-xs {{ $error ? 'text-red-500' : 'text-black/40' }}">{{ $hint }}</p>
    @endif
</label>
