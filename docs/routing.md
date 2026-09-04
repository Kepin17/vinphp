# Routing

All routes live in `routes/web.php`:

```php
use App\Controllers\PostController;

$router->get('/posts', [new PostController(), 'index']);
$router->post('/posts', [new PostController(), 'store']);
```

A route with no match returns 404 and renders `app/Views/pages/404.php`
(`App\Core\Router::dispatch()`).

## Adding a page

1. Generate a controller: `php make controller Post` → `app/Controllers/PostController.php`.
2. Create the view: `app/Views/pages/post.php`. Build the page's body, then
   hand it to the layout with `@Main(...)` (it auto-resolves to
   `templates/main.php` like any other component — see
   [templating.md](templating.md#calling-components)):
   ```blade
   @php
   ob_start();
   @endphp
   <p>Post content here.</p>
   @php
   $content = ob_get_clean();
   @endphp
   @Main(['title' => 'Post', 'content' => $content])
   ```
   `appName` is optional — `templates/main.php` defaults it to
   `config('app_name')` if you don't pass one.
3. Register the route as above.

Routes are matched by exact path only — no `{param}` placeholders yet. Add
that to `App\Core\Router` if a route needs one (e.g. `/posts/{id}`).

## Handling a POST form

Every `POST` route is CSRF-checked automatically in `Router::dispatch()` —
no per-controller boilerplate needed, but every form that posts must include
the token:

```blade
<form method="post" action="/subscribe">
    {!! csrf_field() !!}
    ...
</form>
```

A submission missing or mismatching the token gets HTTP 419 before the
controller even runs. Read submitted data with `App\Core\Request` rather
than touching `$_POST` directly:

```php
use App\Core\Request;

$email = Request::post('email');
```

`redirect(string $path)` (in `app/Core/helpers.php`) sends a `Location`
header and exits — the standard way to respond after a write. See
`app/Controllers/SubscribeController.php` for the full pattern: read input,
validate, `Model::create()`, redirect with a query-param result
(`/?subscribed=1`) that the view checks to show a message. There's no
session-based flash message system yet — this query-param approach is the
lightest thing that works for a single-field form; reach for flash messages
once more than one field needs per-field error feedback.
