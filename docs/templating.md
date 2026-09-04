# Templating

Views don't need `<?php ... ?>` for the common cases. `App\Core\TemplateCompiler`
translates a small Blade-style syntax to plain PHP before the view is
rendered (see `app/Core/View.php`).

## Output

```blade
{{ $value }}      {{-- escaped: htmlspecialchars($value) --}}
{!! $html !!}     {{-- raw, unescaped — only for HTML you already trust,
                       e.g. a $content string built from other views --}}
```

## Conditionals and loops

```blade
@if($user)
    <p>Hi, {{ $user['name'] }}</p>
@elseif($guest)
    <p>Guest</p>
@else
    <p>...</p>
@endif

@foreach($items as $item)
    <li>{{ $item }}</li>
@endforeach
```

`@for(...)` / `@endfor` and `@while(...)` / `@endwhile` work the same way.

## Props

`@props(...)` replaces the manual `??=` + docblock boilerplate with one line.
Only parameters with a default get a generated line; a required parameter
(no `=`) is left alone — `extract()` already set it if it was passed, and
PHP's own "undefined variable" warning is the signal if it wasn't:

```blade
@props(string $label, string $href = '#', string $variant = 'primary')
```

compiles to `$href ??= '#'; $variant ??= 'primary';` (types are documentation
only — nothing enforces them at runtime).

## Children (slots)

`@slot($var) ... @endslot` captures the HTML between the tags into `$var`
(via output buffering), so it can be passed into a component as an ordinary
prop — the same role React's `children` plays:

```blade
@slot($frameworkNote)
    <a href="/docs">See how it's wired →</a>
@endslot

@Card(['title' => 'Zero Framework', 'children' => $frameworkNote])
```

Inside `card.php`, render it with `{!! $children !!}` (raw — it's already
HTML). Give the prop a default (`string $children = ''`) via `@props` so
components work fine without a slot too. Slots aren't stacked/nameable
beyond the variable you assign them to — one `$var` per `@slot`, no nesting
support yet.

## Raw PHP

Anything that isn't output or a control structure — assigning variables,
`ob_start()`, calling `View::component()` directly — goes in a `@php` block:

```blade
@php
    $total = array_sum($prices);
@endphp
```

## Calling components

Any `@PascalCase(args)` compiles to `\App\Core\View::auto('PascalCase', args)`,
which auto-resolves to the matching view file by naming convention — no
registration, no `use function` needed. See [components.md](components.md)
for the convention and the (rarely-needed) override escape hatch.

```blade
@Button(['label' => 'Save', 'variant' => 'primary'])
@Card($feature)
```

## How it works, and its limits

`View::render()` reads the raw view file, runs it through
`TemplateCompiler::compile()`, and `eval()`s the result. That means:

- **No compile cache.** It re-parses the view on every render. Fine for a
  starter's traffic — add filesystem caching keyed by file mtime if this
  ever shows up in a profiler.
- **`__DIR__` / `__FILE__` inside a view point at `View.php`, not the view
  file itself** (an `eval()` quirk). Never build paths with them in a view —
  use `ROOT_PATH` (defined in `public/index.php`) or the `config()` helper
  instead.
- **Stack traces from a view error point into "eval()'d code"**, not the
  original file/line. Trade-off for the terser syntax.
- Existing plain `<?php ?>` PHP in a view still works untouched — the
  compiler only looks for `{{ }}`, `{!! !!}`, and `@word(...)` patterns and
  leaves everything else as-is (so `you@example.com` in a placeholder string
  is safe, it's not followed by `(`).
- **No editor intelligence inside `@php` blocks** — a view file never
  contains a literal `<?php` tag in its raw source (only after compiling),
  so a PHP language server reads the whole file as plain HTML: no
  autocomplete, no hover docs, no auto-import for classes you reference
  there. If you want real IDE support (e.g. VSCode + the Intelephense
  extension — recommended in `.vscode/extensions.json` — will auto-add
  `use` statements when you accept a class suggestion) for a specific piece
  of logic, write that block as a literal `<?php ... ?>` instead of
  `@php ... @endphp`; both compile to the exact same thing, only the literal
  tag is visible to the editor as real PHP.
