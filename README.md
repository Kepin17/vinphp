# VinPHP

Plain PHP starter, no framework. Views are organized elements → fragments →
templates → pages (a simplified atomic design) with a Blade-style template
syntax, styled with Tailwind CSS.

## Run

```bash
composer install
npm install
npm run build:css      # or: npm run dev:css to watch while developing
npm run build:js
php -S localhost:8000 -t public
```

If you're serving through your own vhost (nginx/Apache/Herd) pointed at
`public/`, you only need `npm run dev:css` running — the PHP server above is
a fallback for when no vhost is set up.

## Starting a new project from this starter

```bash
./create-vinphp ../my-new-app
```

Copies this template (minus `node_modules`, build artifacts, `.env`) to
`../my-new-app`, installs dependencies, builds assets, and creates a fresh
`.env`. See [docs/create-project.md](docs/create-project.md).

## Docs

- [docs/structure.md](docs/structure.md) — folder layout
- [docs/templating.md](docs/templating.md) — `{{ }}` / `@if` / `@ComponentName()` syntax
- [docs/components.md](docs/components.md) — elements/fragments views, adding a new one
- [docs/routing.md](docs/routing.md) — routes and controllers
- [docs/database.md](docs/database.md) — models, migrations, the `make` generator
- [docs/styling.md](docs/styling.md) — Tailwind setup and brand colors
- [docs/icons.md](docs/icons.md) — the `icon()` helper (Lucide, raw SVG)
- [docs/security.md](docs/security.md) — CSRF, headers, session cookie, opt-in tools
- [docs/navigation.md](docs/navigation.md) — no-reload navigation (htmx)
- [docs/editor-extension.md](docs/editor-extension.md) — VSCode snippets, as a workspace file or an installable extension
- [docs/create-project.md](docs/create-project.md) — scaffolding a new project from this one
