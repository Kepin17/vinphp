# VSCode extension (no per-project setup)

`vscode-extension/` packages the same snippets as
`.vscode/vinphp.code-snippets` into a real installable VSCode
extension — install it once on your machine and every `.php` file in any
project gets the `@Component`, `@if`/`@foreach`/`@props`/`@slot`, and helper
snippets (`config`, `icon`, `csrf_field`, `redirect`, `Request`, routes), no
copying files per project.

```bash
cd vscode-extension
npx @vscode/vsce package --allow-missing-repository
code --install-extension vinphp-snippets-0.0.1.vsix
```

Reload VSCode. That's it — see `vscode-extension/README.md` for updating
and uninstalling.

Both live side by side on purpose:
- `.vscode/vinphp.code-snippets` — workspace-scoped, ships with this
  repo, no install step. Some VSCode setups don't pick up workspace
  snippets reliably (seen firsthand while building this starter) — the
  extension is the fallback that doesn't depend on that working.
- `vscode-extension/` — installed once, works everywhere, not tied to any
  one repo being open.

If you add or change a component, keep both in sync: run `php snippets`
(updates the workspace file), then copy that into
`vscode-extension/snippets/php.json`, bump the extension's version, and
repackage/reinstall.
