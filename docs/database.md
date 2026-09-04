# Database

No ORM — `App\Models\Model` is a thin base class over PDO
(`all()` / `find($id)` / `create($data)`). Add methods to a specific model as
queries come up.

## Connection & secrets

Copy `.env.example` to `.env` and fill in real credentials:

```bash
cp .env.example .env
```

```
APP_NAME, APP_ENV
DB_HOST, DB_NAME, DB_USER, DB_PASS
```

`.env` is gitignored — never commit it. `App\Core\Env::load()` reads it once
at boot (`public/index.php`, `bin/migrate.php`) via `putenv()`; a real
environment variable of the same name always wins over the file, so
production can override without touching `.env`. `app/Config/database.php`
and `config.php` read these through `getenv()`, with local-dev fallbacks if
a var is unset.

The connection itself is a lazy PDO singleton — `App\Core\Database::connection()`.

## Generating a resource

For a new resource, generate its controller, model, and migration together
so the names and table stay consistent:

```bash
php make resource Post
# -> app/Controllers/PostController.php
# -> app/Models/Post.php               (table: posts)
# -> database/migrations/{timestamp}_create_posts_table.php  (CREATE TABLE posts ...)
```

`php make migration` alone also creates the Controller/Model if they don't
exist yet, inferring the name from the migration name:

```bash
php make migration create_posts_table   # -> Post + PostController + the migration
php make migration test                 # -> Test + TestController + the migration
```

It skips auto-creating them when the name reads as an alteration rather than
a new resource (`add_`, `drop_`, `alter_`, `remove_`, `update_`, `rename_`
prefix) — and skips (not errors) if the Controller/Model already exist:

```bash
php make migration add_status_to_posts   # -> just the migration
```

Or generate just one piece directly:

```bash
php make controller Post
php make model Post
```

`php make` is `bin/make.php`; the `make` file at the project root is a
one-line shortcut for it (`require __DIR__ . '/bin/make.php';`).

Edit the generated migration's closures:

```php
return [
    'up' => function (PDO $db): void {
        $db->exec("CREATE TABLE posts (...)");
    },
    'down' => function (PDO $db): void {
        $db->exec("DROP TABLE IF EXISTS posts");
    },
];
```

## Running migrations

```bash
php migrate          # shortcut for: php bin/migrate.php
```

Runs every file in `database/migrations/` (in filename order, oldest
timestamp first) not yet recorded in the `migrations` table, then records
it. Each migration runs in its own transaction — if one fails, it rolls back
and the run stops (already-applied migrations before it stay applied).

**Foreign key ordering:** the whole batch runs with
`SET FOREIGN_KEY_CHECKS=0`, re-enabled once it finishes (or on failure). This
is the classic Laravel pain point — a table referencing another table via FK
that hasn't been created yet, timestamp order or not (errno 150). Disabling
checks for the run sidesteps it entirely: you don't have to fight migration
ordering to add a FK. Re-enabled checks still validate all data once the
batch is done, so a genuinely broken reference (pointing at a table that
never gets created) still surfaces — just at the next write, not at
migration time.

There's no rollback command — `down()` exists for reference/manual use only.

## Joining tables

`all()`/`find()`/`create()` cover single-table access. For anything that
joins two tables, `Model::query()` is the escape hatch — write the SQL
yourself, still through the same prepared-statement path as everything
else:

```php
Subscriber::query(
    'SELECT s.*, p.name AS plan_name FROM subscribers s
     JOIN plans p ON p.id = s.plan_id WHERE s.id = ?',
    [$id]
);
```

For the common shapes (a row belongs to one related row / a row has many
related rows), two thin helpers sit on top of `query()`/`find()` — no query
builder, no eager loading, just a couple of lines each:

```php
class Subscriber extends Model
{
    protected static string $table = 'subscribers';

    public static function plan(array $subscriber): array|false
    {
        return static::belongsTo(Plan::class, 'plan_id', $subscriber);
    }
}

class Plan extends Model
{
    protected static string $table = 'plans';

    public static function subscribers(int $planId): array
    {
        return static::hasMany(Subscriber::class, 'plan_id', $planId);
    }
}
```

```php
$subscriber = Subscriber::find($id);
$plan = Subscriber::plan($subscriber);        // belongsTo: one related row
$subscribers = Plan::subscribers($plan['id']); // hasMany: many related rows
```

`belongsTo`/`hasMany` are `protected` on `Model` — wrap them in a
named method on your model (like `plan()`/`subscribers()` above) rather than
calling them from outside; that's also where you'd rename them to whatever
reads best for that relationship.
