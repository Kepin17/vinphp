# Icons

Icons come from `lucide-static` (raw SVG files, no JS runtime — installed as
an npm devDependency, read directly off disk by PHP).

```blade
{!! icon('user', ['class' => 'w-5 h-5 text-gray-500']) !!}
```

or, outside the `{{ }}`/`@` syntax, plain PHP works the same way:

```php
<?= icon('user', ['class' => 'w-5 h-5 text-gray-500']) ?>
```

Use `{!! !!}`/`<?=`, not `{{ }}` — `icon()` returns real SVG markup, and
`{{ }}` would HTML-escape it into visible text.

`icon($name, $attributes)`:
- `$name` is the icon's kebab-case filename, e.g. `user` for `user.svg`.
  Browse available names in `node_modules/lucide-static/icons/` or at
  lucide.dev/icons.
- `$attributes` merges into the `<svg>` tag. `class` is merged with the
  library's own `class="lucide lucide-{name}"` (both end up applied) instead
  of producing a duplicate `class` attribute; any other attribute (`width`,
  `height`, `stroke-width`, `aria-hidden`, ...) overrides the SVG's default
  if present, or gets appended if not.
- Throws `RuntimeException` for an unknown icon name (typo-guard — fails
  loudly instead of rendering nothing).
- Parsed SVGs are cached per-request (static array in `icon()`), so reusing
  the same icon many times on one page only reads the file once.

Implementation: `app/Core/helpers.php`. Depends on `node_modules/lucide-static`
being present wherever the app runs — if you deploy without `node_modules`
(PHP-only production host), either deploy it too or copy the specific icons
you use into a project folder and point `icon()` at that instead.
