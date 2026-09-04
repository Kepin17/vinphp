<?php

namespace App\Core;

class View
{
    /**
     * Render a view file with data. Views live under app/Views and are
     * referenced by dot-free relative path, e.g. "pages/home".
     */
    public static function render(string $view, array $data = []): void
    {
        if (!preg_match('#^[a-z0-9/_-]+$#i', $view)) {
            throw new \RuntimeException("Invalid view name: {$view}");
        }

        extract($data);
        $file = dirname(__DIR__) . '/Views/' . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        $compiled = TemplateCompiler::compile(file_get_contents($file));
        eval('?>' . $compiled);
    }

    /**
     * Include a reusable view (element/fragment) from inside another view,
     * e.g. component('elements/button', ['label' => 'Go']).
     */
    public static function component(string $name, array $data = []): void
    {
        self::render($name, $data);
    }

    private const COMPONENT_DIRS = ['elements', 'fragments', 'templates'];

    /**
     * Resolves and renders a component from its PascalCase name, e.g.
     * "FormField" -> app/Views/{elements,fragments,templates}/form-field.php
     * (whichever exists). This is what @ComponentName(...) compiles to, so a
     * new component just needs the view file — no manual registration.
     *
     * A function App\Components\{name}(), if one exists, is called instead —
     * that's the escape hatch for a component that needs logic beyond what a
     * view file's @php block can express (see app/Components/functions.php).
     */
    public static function auto(string $name, array $data = []): void
    {
        $fn = "App\\Components\\{$name}";
        if (function_exists($fn)) {
            $fn($data);
            return;
        }

        self::render(self::resolve($name), $data);
    }

    private static function resolve(string $name): string
    {
        static $cache = [];

        if (isset($cache[$name])) {
            return $cache[$name];
        }

        $kebab = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

        foreach (self::COMPONENT_DIRS as $dir) {
            if (is_file(dirname(__DIR__) . "/Views/{$dir}/{$kebab}.php")) {
                return $cache[$name] = "{$dir}/{$kebab}";
            }
        }

        throw new \RuntimeException(
            "Component not found: {$name} (looked for {$kebab}.php in " . implode(', ', self::COMPONENT_DIRS) . ')'
        );
    }
}
