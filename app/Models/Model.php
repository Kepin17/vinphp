<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected static string $table;

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function all(): array
    {
        return static::db()->query('SELECT * FROM ' . static::$table)->fetchAll();
    }

    public static function find(int $id): array|false
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = static::db()->prepare("INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        return static::db()->lastInsertId();
    }

    public static function table(): string
    {
        return static::$table;
    }

    /** Escape hatch for anything all()/find()/create() don't cover — joins, aggregates, whatever. */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = static::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Given a row you already have, fetch the one related row it points to.
     *   $plan = Subscriber::belongsTo(Plan::class, 'plan_id', $subscriber);
     */
    protected static function belongsTo(string $relatedClass, string $foreignKey, array $row): array|false
    {
        return $relatedClass::find((int) ($row[$foreignKey] ?? 0));
    }

    /**
     * Fetch every row in another table pointing back at this one.
     *   $subscribers = Plan::hasMany(Subscriber::class, 'plan_id', $planId);
     */
    protected static function hasMany(string $relatedClass, string $foreignKey, int $ownerId): array
    {
        return $relatedClass::query(
            "SELECT * FROM {$relatedClass::table()} WHERE {$foreignKey} = ?",
            [$ownerId]
        );
    }
}
