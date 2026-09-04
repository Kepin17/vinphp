<?php

/**
 * Usage: php snippets  (or php bin/snippets.php)
 *
 * Scans app/Views/{elements,fragments,templates} and adds a snippet
 * entry to .vscode/vinphp.code-snippets for any component that doesn't
 * have one yet — so a new component gets autocomplete without hand-editing
 * the snippets file. Existing entries (including hand-curated ones like
 * Button's variant dropdown) are left untouched; this only fills gaps.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$snippetsPath = "{$root}/.vscode/vinphp.code-snippets";
$dirs = ['elements', 'fragments', 'templates'];

$snippets = is_file($snippetsPath) ? json_decode(file_get_contents($snippetsPath), true) : [];

// Which component names already have an entry? (a snippet whose body calls @Name(...))
$covered = [];
foreach ($snippets as $entry) {
    $body = is_array($entry['body']) ? implode('', $entry['body']) : $entry['body'];
    if (preg_match('/^@(\w+)\(/', $body, $m)) {
        $covered[$m[1]] = true;
    }
}

function kebabToPascal(string $kebab): string
{
    return str_replace('-', '', ucwords($kebab, '-'));
}

/** Reads a view file's @props(...) param names, e.g. ["label", "variant"]. */
function propNames(string $file): array
{
    $content = file_get_contents($file);
    if (!preg_match('/@props\(([^)]*)\)/s', $content, $m)) {
        return [];
    }

    $names = [];
    foreach (explode(',', $m[1]) as $param) {
        if (preg_match('/\$(\w+)/', $param, $pm)) {
            $names[] = $pm[1];
        }
    }

    return $names;
}

$added = [];

foreach ($dirs as $dir) {
    foreach (glob("{$root}/app/Views/{$dir}/*.php") ?: [] as $file) {
        $pascal = kebabToPascal(basename($file, '.php'));

        if (isset($covered[$pascal])) {
            continue;
        }

        $props = propNames($file);
        $pairs = [];
        $i = 1;
        foreach ($props as $prop) {
            $pairs[] = "'{$prop}' => '\${$i}'";
            $i++;
        }
        $body = $pairs
            ? "@{$pascal}([" . implode(', ', $pairs) . '])$0'
            : "@{$pascal}([\$1])\$0";

        $snippets["{$pascal} (auto)"] = [
            'scope' => 'php',
            'prefix' => [$pascal, "@{$pascal}"],
            'body' => $body,
            'description' => "Insert a {$pascal} component call (auto-generated from {$dir}/" . basename($file) . ')',
        ];

        $covered[$pascal] = true;
        $added[] = $pascal;
    }
}

if (!$added) {
    echo "Nothing to add — every component already has a snippet.\n";
    exit;
}

file_put_contents($snippetsPath, json_encode($snippets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo 'Added snippets for: ' . implode(', ', $added) . "\n";
