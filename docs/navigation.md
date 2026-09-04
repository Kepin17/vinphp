# No-reload navigation (htmx)

Every internal link and form navigates without a full page reload, via
[htmx](https://htmx.org)'s `hx-boost`, set on `<body>` in
`app/Views/templates/main.php`:

```html
<body hx-boost="true">
```

## How it works

`hx-boost` intercepts clicks on same-origin `<a>` tags and form submits,
fetches the target page as a normal `GET`/`POST`, then swaps `<body>`'s
content and updates the URL/history — no backend changes needed, since the
server still just renders full pages like always (`templates/main.php`).
Progressive enhancement: if JavaScript fails to load, links and forms fall
back to normal full-page navigation automatically.

## Opting a link or section out

```blade
<a href="/download/file.zip" hx-boost="false">Download</a>
```

or on a container, to exclude everything inside it:

```blade
<div hx-boost="false">
    ...
</div>
```

An `<a target="_blank">` or a link to a different origin is never boosted,
even without the attribute.

## The script

`node_modules/htmx.org` is a devDependency; `npm run build:js` copies its
minified build to `public/assets/js/htmx.min.js` (gitignored, same pattern
as `public/assets/css/app.css` — regenerate it, don't hand-edit it, don't
commit it).

## CSP note

`public/index.php`'s Content-Security-Policy allows `style-src 'self'
'unsafe-inline'` — htmx applies inline styles during some swaps/transitions,
which the stricter `default-src 'self'` alone would silently block (it
showed up as a browser console CSP violation during testing, not a visible
bug, so it's easy to miss if you tighten this header later). `script-src`
stays locked to `'self'` — only styles are relaxed, not scripts.
