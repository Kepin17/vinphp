<?php

namespace App\Core;

/**
 * Compiles a lightweight Blade-style syntax to plain PHP:
 *   {{ expr }}   -> escaped echo
 *   {!! expr !!} -> raw echo
 *   @if/@elseif/@else/@endif, @foreach/@endforeach, @for/@endfor, @while/@endwhile
 *   @php ... @endphp  -> raw PHP block
 *   @ComponentName(args) -> calls \App\Core\View::auto('ComponentName', args),
 *     which resolves the view by convention (FormField -> form-field.php in
 *     elements/fragments/templates) unless a App\Components\ComponentName()
 *     override function exists — see View::auto()
 *   @props(type $name = default, ...) -> one-line prop defaults ($name ??= default;)
 *   @slot($var) ... @endslot -> captures the HTML between them into $var,
 *     for passing rendered content into a component as a prop (React-children style)
 *
 * View::render() memoizes compiled output per file for the lifetime of the
 * PHP process (see its static $compiled cache) — a view is parsed once per
 * process, not once per render call, so a loop calling the same component
 * many times only pays the parse cost once. No cross-request/disk cache
 * though: under `php -S` every request is a fresh process anyway, and under
 * php-fpm a worker only sees a view's latest content on its next recycle —
 * fine for a starter's traffic and dev workflow.
 */
class TemplateCompiler
{
    private const CONTROL = ['if', 'elseif', 'for', 'foreach', 'while'];
    private const END = ['endif', 'endforeach', 'endfor', 'endwhile'];

    public static function compile(string $template): string
    {
        $template = self::compileDirectives($template);
        $template = preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?= $1 ?>', $template);
        $template = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?= htmlspecialchars((string) ($1)) ?>', $template);

        return $template;
    }

    private static function compileDirectives(string $tpl): string
    {
        $result = '';
        $len = strlen($tpl);
        $i = 0;
        $slotStack = [];

        while ($i < $len) {
            if ($tpl[$i] === '@' && preg_match('/\G@([A-Za-z_][A-Za-z0-9_]*)/', $tpl, $m, 0, $i)) {
                $word = $m[1];
                $after = $i + strlen($m[0]);
                $args = null;
                $next = $after;

                if ($next < $len && $tpl[$next] === '(') {
                    [$args, $next] = self::extractParens($tpl, $next);
                }

                if ($word === 'slot' && $args !== null) {
                    $slotStack[] = trim($args);
                    $result .= '<?php ob_start(); ?>';
                    $i = $next;
                    continue;
                }

                if ($word === 'endslot') {
                    $var = array_pop($slotStack) ?? '$slot';
                    $result .= "<?php {$var} = ob_get_clean(); ?>";
                    $i = $next;
                    continue;
                }

                $php = self::toPhp($word, $args);
                if ($php !== null) {
                    $result .= $php;
                    $i = $next;
                    continue;
                }
            }

            $result .= $tpl[$i];
            $i++;
        }

        return $result;
    }

    /** @return array{0: string, 1: int} [args inside the parens, position right after the closing paren] */
    private static function extractParens(string $tpl, int $start): array
    {
        $depth = 0;
        $len = strlen($tpl);

        for ($j = $start; $j < $len; $j++) {
            if ($tpl[$j] === '(') {
                $depth++;
            } elseif ($tpl[$j] === ')') {
                $depth--;
                if ($depth === 0) {
                    return [substr($tpl, $start + 1, $j - $start - 1), $j + 1];
                }
            }
        }

        return [substr($tpl, $start + 1), $len];
    }

    private static function toPhp(string $word, ?string $args): ?string
    {
        if (in_array($word, self::CONTROL, true) && $args !== null) {
            return "<?php {$word} ({$args}): ?>";
        }

        if ($word === 'else') {
            return '<?php else: ?>';
        }

        if (in_array($word, self::END, true)) {
            return "<?php {$word}; ?>";
        }

        if ($word === 'php') {
            return '<?php';
        }

        if ($word === 'endphp') {
            return '?>';
        }

        if ($word === 'props' && $args !== null) {
            return self::compileProps($args);
        }

        if ($args !== null && ctype_upper($word[0])) {
            return "<?php \\App\\Core\\View::auto('{$word}', {$args}); ?>";
        }

        return null;
    }

    /**
     * "type $name = default, type $name2, ..." -> one $name ??= default; per
     * parameter that has a default. A parameter with no default is left
     * alone (extract() already set it, or PHP's own "undefined variable"
     * warning is the signal that a required prop was never passed).
     */
    private static function compileProps(string $args): string
    {
        $lines = [];

        foreach (self::splitTopLevel($args) as $param) {
            $param = trim($param);
            if ($param === '') {
                continue;
            }

            if (preg_match('/\$(\w+)(?:\s*=\s*(.+))?$/s', $param, $m) && isset($m[2])) {
                $lines[] = "\${$m[1]} ??= {$m[2]};";
            }
        }

        return '<?php ' . implode(' ', $lines) . ' ?>';
    }

    /** Split on top-level commas only — ignores commas inside (), [], or quoted strings. */
    private static function splitTopLevel(string $args): array
    {
        $parts = [];
        $buffer = '';
        $depth = 0;
        $quote = null;
        $len = strlen($args);

        for ($i = 0; $i < $len; $i++) {
            $ch = $args[$i];

            if ($quote !== null) {
                $buffer .= $ch;
                if ($ch === $quote && $args[$i - 1] !== '\\') {
                    $quote = null;
                }
                continue;
            }

            if ($ch === '"' || $ch === "'") {
                $quote = $ch;
                $buffer .= $ch;
            } elseif ($ch === '(' || $ch === '[') {
                $depth++;
                $buffer .= $ch;
            } elseif ($ch === ')' || $ch === ']') {
                $depth--;
                $buffer .= $ch;
            } elseif ($ch === ',' && $depth === 0) {
                $parts[] = $buffer;
                $buffer = '';
            } else {
                $buffer .= $ch;
            }
        }

        if (trim($buffer) !== '') {
            $parts[] = $buffer;
        }

        return $parts;
    }
}
