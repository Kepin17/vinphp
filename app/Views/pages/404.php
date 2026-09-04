@php
$appName = config('app_name');
ob_start();
@endphp
<section class="mx-auto flex max-w-6xl flex-col items-center px-6 py-32 text-center">
    @Heading(['text' => '404 — Page Not Found', 'level' => 'h1'])
    <p class="mt-4 text-black/60">The page you're looking for doesn't exist.</p>
    <div class="mt-8">
        @Button(['label' => 'Back Home', 'href' => '/', 'variant' => 'primary'])
    </div>
</section>
@php
$content = ob_get_clean();

\App\Core\View::component('templates/main', [
    'title' => 'Not Found',
    'appName' => $appName,
    'content' => $content,
]);
@endphp
