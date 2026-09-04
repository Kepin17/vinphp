<?php

/**
 * Global helpers, available in every view/controller. Path-independent
 * (uses ROOT_PATH, not __DIR__), so they work the same whether a view is
 * require'd or eval'd by the template compiler.
 */

function config(?string $key = null): mixed
{
    static $items = null;

    if ($items === null) {
        $items = require ROOT_PATH . '/app/Config/config.php';
    }

    return $key === null ? $items : ($items[$key] ?? null);
}

/** Hidden CSRF input — put inside every <form method="post">. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(\App\Core\Csrf::token()) . '">';
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * Renders a Lucide icon (raw SVG from lucide-static, no JS runtime).
 *   icon('user', ['class' => 'w-5 h-5 text-gray-500'])
 * $name is kebab-case, matching the file in lucide-static/icons/*.svg.
 */
function icon(string $name, array $attributes = []): string
{
    static $cache = [];

    if (!isset($cache[$name])) {
        $path = ROOT_PATH . "/node_modules/lucide-static/icons/{$name}.svg";
        if (!is_file($path)) {
            throw new \RuntimeException("Unknown icon: {$name}");
        }
        // Strip the leading "@license" comment lucide-static ships in every file.
        $cache[$name] = preg_replace('/^<!--.*?-->\s*/s', '', file_get_contents($path));
    }

    $svg = $cache[$name];

    // lucide-static SVGs already ship a `class="lucide lucide-{name}"` — merge
    // instead of ending up with two class attributes.
    if (isset($attributes['class']) && preg_match('/<svg[^>]*\sclass="([^"]*)"/', $svg, $m)) {
        $attributes['class'] = trim($m[1] . ' ' . $attributes['class']);
    }

    foreach ($attributes as $key => $value) {
        $escaped = htmlspecialchars((string) $value);
        $pattern = '/(<svg\b[^>]*?)\s' . preg_quote((string) $key, '/') . '="[^"]*"/';

        $svg = preg_match($pattern, $svg)
            ? preg_replace($pattern, '$1 ' . $key . '="' . $escaped . '"', $svg, 1)
            : preg_replace('/<svg/', '<svg ' . $key . '="' . $escaped . '"', $svg, 1);
    }

    return $svg;
}
