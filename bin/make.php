<?php

/**
 * Scaffold generator.
 *
 * Usage:
 *   php make controller Post   -> app/Controllers/PostController.php
 *   php make model Post        -> app/Models/Post.php
 *   php make migration create_posts_table
 *                               -> database/migrations/{timestamp}_create_posts_table.php,
 *                                  plus the Controller/Model for "Post" if
 *                                  they don't exist yet (skipped for
 *                                  alteration-style names like add_x_to_y)
 *   php make resource Post     -> controller + model + migration together,
 *                                  all named/tabled consistently (the usual
 *                                  case: a new resource needs all three)
 */

declare(strict_types=1);

$root = dirname(__DIR__);
[$type, $name] = [$argv[1] ?? null, $argv[2] ?? null];

if (!$type || !$name) {
    fwrite(STDERR, "Usage: php make <controller|model|migration|resource> <Name>\n");
    exit(1);
}

function tableName(string $class): string
{
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
}

function controllerStub(string $root, string $name): array
{
    $class = str_ends_with($name, 'Controller') ? $name : "{$name}Controller";
    $view = strtolower(preg_replace('/Controller$/', '', $class));
    $path = "{$root}/app/Controllers/{$class}.php";
    $stub = <<<PHP
    <?php

    namespace App\Controllers;

    use App\Core\View;

    class {$class}
    {
        public function index(): void
        {
            View::render('pages/{$view}', []);
        }
    }

    PHP;

    return [$path, $stub];
}

function modelStub(string $root, string $name): array
{
    $class = ucfirst($name);
    $table = tableName($class);
    $path = "{$root}/app/Models/{$class}.php";
    $stub = <<<PHP
    <?php

    namespace App\Models;

    class {$class} extends Model
    {
        protected static string \$table = '{$table}';
    }

    PHP;

    return [$path, $stub];
}

function migrationStub(string $root, string $migrationName, string $table): array
{
    $timestamp = date('Y_m_d_His');
    $path = "{$root}/database/migrations/{$timestamp}_{$migrationName}.php";
    $stub = <<<PHP
    <?php

    use PDO;

    return [
        'up' => function (PDO \$db): void {
            \$db->exec("CREATE TABLE {$table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            )");
        },
        'down' => function (PDO \$db): void {
            \$db->exec("DROP TABLE IF EXISTS {$table}");
        },
    ];

    PHP;

    return [$path, $stub];
}

function write(string $path, string $stub): void
{
    if (is_file($path)) {
        fwrite(STDERR, "Already exists: {$path}\n");
        exit(1);
    }

    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, $stub);
    echo "Created: {$path}\n";
}

/** Like write(), but silently skips instead of failing when the file already exists. */
function writeIfMissing(string $path, string $stub): void
{
    if (is_file($path)) {
        echo "Skipped (already exists): {$path}\n";
        return;
    }

    @mkdir(dirname($path), 0755, true);
    file_put_contents($path, $stub);
    echo "Created: {$path}\n";
}

/**
 * "create_posts_table" -> "Post". Returns null for names that read as an
 * alteration (add_x_to_y, drop_x_from_y, ...) rather than a new resource —
 * those shouldn't spawn a controller/model.
 */
function resourceNameFromMigration(string $migrationName): ?string
{
    foreach (['add_', 'drop_', 'alter_', 'remove_', 'update_', 'rename_'] as $prefix) {
        if (str_starts_with($migrationName, $prefix)) {
            return null;
        }
    }

    $stripped = preg_replace('/^create_/', '', $migrationName);
    $stripped = preg_replace('/_table$/', '', $stripped);
    $singular = rtrim($stripped, 's');

    return ucfirst($singular);
}

switch ($type) {
    case 'controller':
        write(...controllerStub($root, $name));
        break;

    case 'model':
        write(...modelStub($root, $name));
        break;

    case 'migration':
        $resourceClass = resourceNameFromMigration($name);
        $table = $resourceClass ? tableName($resourceClass) : 'example';

        write(...migrationStub($root, $name, $table));

        if ($resourceClass) {
            writeIfMissing(...controllerStub($root, $resourceClass));
            writeIfMissing(...modelStub($root, $resourceClass));
        }
        break;

    case 'resource':
        $class = ucfirst($name);
        $table = tableName($class);
        write(...controllerStub($root, $class));
        write(...modelStub($root, $class));
        write(...migrationStub($root, "create_{$table}_table", $table));
        break;

    default:
        fwrite(STDERR, "Unknown type '{$type}'. Use controller, model, migration, or resource.\n");
        exit(1);
}
