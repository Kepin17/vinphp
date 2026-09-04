# Styling

Tailwind CSS v4, compiled via the CLI (`@tailwindcss/cli`, no config file —
v4 auto-scans project files for class names).

```bash
npm install
npm run dev:css     # watch mode while developing
npm run build:css   # one-off minified build, run before deploy
```

Edit `resources/css/app.css` (the source); `public/assets/css/app.css` is
the compiled, gitignored output — never edit it directly.

`npm run dev:css` uses `--watch=always` rather than `--watch`: plain
`--watch` stops as soon as it can't read stdin (true in a background
process/script), so the first build never even runs. `=always` keeps
watching regardless.

## Brand colors

Defined in `resources/css/app.css` under `@theme`, then used as ordinary
Tailwind utilities:

```css
@theme {
    --color-brand-teal: #258C93;
    --color-brand-orange: #FD8C02;
}
```

```html
<div class="bg-brand-orange text-black hover:bg-brand-orange/90">
```

Plus Tailwind's built-in `black` / `white`. There's no generated shade scale
(`brand-teal-100`, `-700`, etc.) — use opacity modifiers (`/60`, `/10`)
against the solid color instead; add a real scale if a design needs distinct
tints later.

## Conditional styling

Prefer Tailwind's variant prefixes over custom CSS for state:

```
hover: focus: disabled: dark: md: lg:
data-[key=value]: has-[selector]: group-hover: peer-checked:
```

Only add a custom class in `resources/css/app.css` (`@layer components`) when
the same combination of utilities repeats across many places.
