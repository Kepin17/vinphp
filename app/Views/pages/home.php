@props(string $title = 'Home')
@slot($frameworkNote)
    <a href="/docs" class="text-xs font-semibold text-brand-teal underline">See how it's wired →</a>
@endslot
@php
$features = [
    ['title' => 'Composable Views', 'description' => 'Reusable elements and fragments compose every page.'],
    ['title' => 'Tailwind CSS', 'description' => 'Utility-first styling with a compiled, production-ready build.'],
    ['title' => 'Zero Framework', 'description' => 'A tiny router and view layer — nothing to fight against.', 'children' => $frameworkNote],
];
@endphp
@slot($content)
    <section class="mx-auto max-w-6xl px-6 py-20 text-center">
        @Heading(['text' => 'Build faster with a clean starter', 'level' => 'h1'])
        <p class="mx-auto mt-4 max-w-xl text-black/60">
            Plain PHP, no framework overhead, views organized as elements,
            fragments, templates and pages — styled with Tailwind CSS.
        </p>
        <div class="mt-8 flex justify-center gap-3">
            @Button(['label' => 'Get Started', 'variant' => 'primary'])
            @Button(['label' => 'Learn More', 'variant' => 'secondary'])
        </div>
    </section>

    <section id="features" class="mx-auto max-w-6xl px-6 pb-20">
        <div class="grid gap-6 sm:grid-cols-3">
            @foreach($features as $feature)
                @Card($feature)
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-md px-6 pb-24 text-center">
        <div class="flex justify-center">
            {!! icon('mail', ['class' => 'w-6 h-6 text-brand-teal']) !!}
        </div>
        @Heading(['text' => 'Get updates', 'level' => 'h2'])
        @if(\App\Core\Request::get('subscribed') === '1')
            <p class="mt-4 text-sm font-medium text-brand-teal">Thanks, you're subscribed!</p>
        @elseif(\App\Core\Request::get('subscribed') === '0')
            <p class="mt-4 text-sm font-medium text-red-500">That doesn't look like a valid email.</p>
        @elseif(\App\Core\Request::get('subscribed') === 'throttled')
            <p class="mt-4 text-sm font-medium text-red-500">Too many attempts — try again in a minute.</p>
        @endif
        <form method="post" action="/subscribe" class="mt-4 flex items-start gap-2">
            {!! csrf_field() !!}
            <div class="flex-1">
                @FormField([
                    'label' => 'Email',
                    'name' => 'email',
                    'type' => 'email',
                    'placeholder' => 'you@example.com',
                    'hint' => "We'll never share your email.",
                ])
            </div>
            @Button(['label' => 'Subscribe', 'variant' => 'primary', 'type' => 'submit'])
        </form>
    </section>
@endslot
@Main(['title' => $title, 'content' => $content])
