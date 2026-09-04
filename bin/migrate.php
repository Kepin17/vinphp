<?php

/**
 * Usage: php bin/migrate.php  (or the shortcut: php migrate)
 * Runs every migration in database/migrations/ not yet recorded in the
 * `migrations` table, in filename order.
 *
 * Foreign key checks are disabled for the whole batch, so it doesn't matter
 * if migration A references a table that migration B (created later in the
 * same run) hasn't made yet — the classic Laravel "1005 errno 150" ordering
 * error. Checks are re-enabled once every migration has run (or on failure).
 */

declare(strict_types=1);

require dirname(__DIR__) . '/app/Core/Env.php';
require dirname(__DIR__) . '/app/Core/Database.php';

use App\Core\Database;
use App\Core\Env;

Env::load(dirname(__DIR__) . '/.env');

$db = Database::connection();

$db->exec('CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    run_at DATETIME NOT NULL
)');

$ran = $db->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$dir = dirname(__DIR__) . '/database/migrations';
$files = glob($dir . '/*.php') ?: [];
sort($files);

$pending = array_filter($files, fn (string $file) => !in_array(basename($file, '.php'), $ran, true));

if (!$pending) {
    echo "Nothing to migrate.\n";
    exit;
}

$db->exec('SET FOREIGN_KEY_CHECKS=0');

try {
    foreach ($pending as $file) {
        $name = basename($file, '.php');
        $migration = require $file;

        $db->beginTransaction();
        try {
            $migration['up']($db);
            $stmt = $db->prepare('INSERT INTO migrations (migration, run_at) VALUES (?, NOW())');
            $stmt->execute([$name]);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw new \RuntimeException("Migration failed: {$name} — {$e->getMessage()}", 0, $e);
        }

        echo "Migrated: {$name}\n";
    }
} finally {
    $db->exec('SET FOREIGN_KEY_CHECKS=1');
}
