# Creating a new project

```bash
./create-vinphp ../my-new-app
```

Copies this starter into `../my-new-app` and bootstraps it:

- Excludes `.git`, `node_modules`, `vendor`, `.env`, and the built
  `app.css`/`htmx.min.js` — the new project gets clean sources, not this
  one's build output or history.
- `composer install` (skipped with a message if `composer` isn't on your
  PATH — run it yourself later), `npm install`, `npm run build:css`,
  `npm run build:js`.
- Copies `.env.example` → `.env` (already `APP_NAME="VinPHP"` — change it,
  and `DB_*`, for the new project).

Then:

```bash
cd ../my-new-app
$EDITOR .env
php -S localhost:8000 -t public   # or point your own vhost at its public/
```

## Requirements

`rsync`, `node`/`npm`, `php` on the machine running the script — same as
everything else in this starter. `composer` is optional (there are no PHP
package dependencies today, just the PSR-4 autoload map — `composer install`
only generates `vendor/autoload.php`).

## What it doesn't do

- Doesn't rename anything beyond `.env` — the new project's `package.json`
  `name` field, `README.md`, etc. still say "vinphp" / "VinPHP". Rename
  those by hand if the new project needs its own identity beyond
  `APP_NAME`.
- Doesn't `git init` the new project or touch git at all.
- Doesn't install the [VSCode extension](editor-extension.md) — that's
  global to your editor, install it once and it covers every project
  including ones made this way.
