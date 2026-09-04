# VinPHP Snippets

VSCode snippets for the VinPHP starter: `@Component(...)` calls,
Blade-style directives (`@if`, `@foreach`, `@props`, `@slot`, ...), and
helper functions (`config`, `icon`, `csrf_field`, `redirect`, `Request`,
route registration).

This is the same snippet set as the starter's own
`.vscode/vinphp.code-snippets`, packaged as a real extension so it works in
**every** project without copying that file in each time — install once,
done.

## Build and install

```bash
cd vscode-extension
npx @vscode/vsce package --allow-missing-repository
code --install-extension vinphp-snippets-0.0.1.vsix
```

Reload VSCode (or restart it). The snippets are now active in any `.php`
file, in any project — no per-project setup.

## Updating

Edit `snippets/php.json`, bump `version` in `package.json`, then repeat the
package + install steps above (or run `php snippets` in the starter project
first if you want to pull in newly auto-generated component entries — see
`docs/components.md` — then copy the result into `snippets/php.json`).

## Uninstall

```bash
code --uninstall-extension vinphp.vinphp-snippets
```
