# Components

Views are organized by how reusable/composed they are — simplified from
[atomic design](https://atomicdesign.bradfrost.com/) into two folders
instead of three, so it's easier to place a new file without having to
learn the full atoms/molecules/organisms vocabulary:

- **elements** — the smallest reusable pieces, styled but not composed of
  anything else: `button`, `heading`, `input`
- **fragments** — everything built from elements, or standalone —
  a small composed piece (`card`, `form-field`) or a whole page section
  (`navbar`, `footer`). If you're unsure whether something is small enough
  to be an element, it isn't — put it in `fragments`.
- **templates** — the page shell/layout: `main.php`
- **pages** — actual routed pages: `home`, `404`

## Adding a new component

Just create the view file — no registration step. `@Badge(...)` resolves
by naming convention (`App\Core\View::auto()`): PascalCase → kebab-case,
searched across `elements/`, `fragments/`, `templates/` in that order,
first match wins.

```blade
{{-- app/Views/elements/badge.php --}}
@props(string $text)
<span class="rounded-full bg-brand-teal/10 px-3 py-1 text-xs font-medium text-brand-teal">
    {{ $text }}
</span>
```

```blade
@Badge(['text' => 'New'])
```

That's it — no wrapper function, nothing to import. PascalCase is what
makes this safe: it can't collide with PHP's own — case-insensitive —
global functions like `header()` or `list()`.

## Editor autocomplete for a new component

`.vscode/vinphp.code-snippets` gives `@Badge(...)` an autocomplete
entry as you type. Run this after adding a component so it doesn't have to
be typed out by hand:

```bash
php snippets   # scans app/Views/** and adds an entry for anything missing
```

It only *adds* entries for components that don't have one yet — hand-tuned
ones (like `Button`'s variant dropdown) are never touched or regenerated.
The generated entry's placeholders come from the component's `@props(...)`
declaration, so give a component real prop names before running it.

## Escape hatch: overriding a component

If a component ever needs real logic beyond what its view file's `@php`
block can express (calling another class, short-circuiting the render
entirely), define a function of the same name in
`app/Components/functions.php` — `View::auto()` checks for one before
falling back to the convention-resolved view, so it takes priority:

```php
namespace App\Components;

function Badge(array $data = []): void
{
    // ... whatever the view file's @php block can't do ...
    \App\Core\View::component('elements/badge', $data);
}
```

This is the exception, not the default — most components need nothing here.
