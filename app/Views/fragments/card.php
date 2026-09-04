@props(string $title, string $description, string $children = '')
<div class="rounded-xl border border-brand-teal/15 bg-white p-6 shadow-sm">
    @Heading(['text' => $title, 'level' => 'h3'])
    <p class="mt-2 text-sm text-black/60">{{ $description }}</p>
    @if($children !== '')
        <div class="mt-3">{!! $children !!}</div>
    @endif
</div>
