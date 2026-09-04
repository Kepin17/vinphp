<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            View::render('pages/404');
            return;
        }

        if ($method === 'POST' && !Csrf::verify(Request::post('_csrf'))) {
            http_response_code(419);
            echo 'Page expired, please go back and try again.';
            return;
        }

        $handler();
    }
}
