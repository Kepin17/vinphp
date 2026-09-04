@php
/**
 * @var string $text
 * @var string $level h1|h2|h3
 */
$level ??= 'h2';
$sizes = [
    'h1' => 'text-4xl font-bold tracking-tight text-black',
    'h2' => 'text-2xl font-semibold text-black',
    'h3' => 'text-lg font-semibold text-black',
];
$tag = in_array($level, ['h1', 'h2', 'h3'], true) ? $level : 'h2';
@endphp
<{{ $tag }} class="{{ $sizes[$tag] }}">{{ $text }}</{{ $tag }}>
