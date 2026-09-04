# Structure

```
composer.json                   PSR-4 autoload map (App\ -> app/), no runtime dependencies
public/index.php                front controller: defines ROOT_PATH, loads vendor/autoload.php, bootstraps routes
routes/web.php                  route definitions ($router->get/post)

app/Core/
  Router.php                    matches METHOD+path to a handler, 404s otherwise
  View.php                      compiles + renders a view file with data
  TemplateCompiler.php          the {{ }} / @if / @ComponentName() -> PHP compiler
  Database.php                  PDO singleton
  helpers.php                   global functions available everywhere (config())

app/Controllers/                one class per resource, calls View::render()
app/Models/                     Model base class (all/find/create over PDO)
app/Components/functions.php    optional per-component override (rarely needed — components
                                 auto-resolve from app/Views/** by naming convention)
app/Config/                     config.php (app settings), database.php (DB credentials)

app/Views/
  elements/                     smallest reusable pieces (button, heading, input)
  fragments/                    elements composed together, or standalone page
                                 sections (card, form-field, navbar, footer)
  templates/                    page shell/layout (main.php)
  pages/                        actual routed pages (home, 404)

database/migrations/            PHP migration files (up()/down())
resources/css/app.css           Tailwind source — edit this
public/assets/css/app.css       compiled output, gitignored — never edit directly

bin/make.php, bin/migrate.php   scaffolding + migration runner
make                             shortcut for `php bin/make.php` (php make ...)
```

Why no framework: this is a starter, not a product. Every piece above is small
enough to read in one sitting — reach for a real framework once the app
outgrows this file count.
