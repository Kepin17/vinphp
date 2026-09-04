@php
/**
 * @var string $title
 * @var string $appName
 * @var string $content  Rendered inner HTML for the page slot.
 */
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} | {{ $title }}</title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/htmx.min.js"></script>
</head>
<body class="flex min-h-screen flex-col bg-white" hx-boost="true">
    @Navbar(['appName' => $appName])

    <main class="flex-1">
        {!! $content !!}
    </main>

    @Footer(['appName' => $appName])
</body>
</html>
