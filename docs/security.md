# Security

## What's on by default

- **CSRF** — every `POST` route is checked in `App\Core\Router::dispatch()`
  before its handler runs (`App\Core\Csrf`). Every form that posts needs
  `{!! csrf_field() !!}`. See [routing.md](routing.md#handling-a-post-form).
- **SQL injection** — `App\Models\Model` uses PDO prepared statements
  everywhere (`find`, `create`). Keep doing that in any query you add by
  hand — never interpolate a variable into SQL.
- **XSS** — `{{ $value }}` auto-escapes via `htmlspecialchars`. Use
  `{!! $value !!}` only for HTML you generated yourself (see
  [templating.md](templating.md#output)).
- **Secrets** — `.env` is gitignored; commit `.env.example` instead. Never
  put a real credential in a `.php` file. See [database.md](database.md#connection--secrets).
- **Security headers** — set on every response in `public/index.php`:
  `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`,
  `Referrer-Policy: strict-origin-when-cross-origin`,
  `Content-Security-Policy: default-src 'self'`, and an explicit
  `Content-Type: text/html; charset=UTF-8`. If you add a script from a CDN
  or an inline `<script>`, the CSP will block it — loosen `script-src`
  there rather than dropping the header entirely.
- **Session cookie** — `session_set_cookie_params()` (also in
  `public/index.php`) sets `httponly` (JS can't read the cookie) and
  `samesite=Lax`. `secure` is set automatically when the request is HTTPS —
  it's *not* forced on, since that would silently break the cookie over
  local `http://`. Once you deploy behind HTTPS, this becomes effective
  with no code change.
- **Error detail** — controlled by `APP_ENV` in `.env`. With
  `APP_ENV=development`, PHP shows full stack traces (file paths, line
  numbers) — useful locally, dangerous in public. With anything else
  (`APP_ENV=production`), error detail is suppressed (`display_errors` off);
  the response is still the correct status code (e.g. 500), just with no
  information disclosed in the body. **Set `APP_ENV=production` before
  deploying anywhere public.** This is intentionally minimal — no pretty
  error page or file logger yet, just closing the leak.
- **View-name allowlist** — `App\Core\View::render()` rejects any `$view`
  argument with characters outside `[a-z0-9/_-]`. No route currently passes
  user input into a view name, so this is defense-in-depth for if that ever
  changes, not a fix for a live bug.

## Opt-in tools (exist, but do nothing until you call them)

- **Password hashing** — `App\Core\Password::hash($plain)`,
  `::verify($plain, $hash)`, `::needsRehash($hash)`. Thin wrappers over
  PHP's own `password_hash`/`password_verify`/`password_needs_rehash`
  (`PASSWORD_DEFAULT`, which PHP upgrades over time — that's what
  `needsRehash()` is for: check it after a successful `verify()` and
  re-hash+save if true). No auth system exists yet to call this from; it's
  here for whenever one gets built.
- **Rate limiting** — `App\Core\Throttle::attempt($key, maxAttempts: 5, decaySeconds: 60)`
  returns `false` once a session has hit the limit within the window
  (`$_SESSION`-backed, no new storage). Nothing calls this automatically —
  add it to a controller wherever you want a limit, e.g.
  `app/Controllers/SubscribeController.php`'s `store()` does exactly this
  (429 + redirect on the 6th attempt within 60s). Per-session, not
  per-IP — fine for slowing down casual form spam, not a substitute for
  IP-based limiting at the edge (nginx/Cloudflare) against a real attacker.
- **CORS** — `App\Core\Cors::handle()` (called once, early, in
  `public/index.php`) reads `app/Config/cors.php`. `allowed_origins` is
  empty by default, which makes the whole thing a no-op — a same-origin app
  needs nothing here. Add an origin (or `'*'`) to turn it on; it then
  answers matching `Origin` requests with the right `Access-Control-*`
  headers and short-circuits an `OPTIONS` preflight with `204` (otherwise
  `Router` would 404 it — no `OPTIONS` routes are registered).

## What's not included, on purpose

- **HSTS** (`Strict-Transport-Security`) — deliberately not set. It's only
  safe to send once you're certain the site is permanently on HTTPS; sending
  it over plain http (or before a cert is in place) can lock out visitors'
  browsers from ever loading the http version again. Add it in your web
  server config once HTTPS is confirmed working, not here.
- **Session ID regeneration on login** — there's no login yet. When one
  gets built, call `session_regenerate_id(true)` right after a successful
  `Password::verify()`, before anything else touches `$_SESSION`.
