<?php

/**
 * Register routes here. Add new ones with $router->get()/post().
 * Example: $router->get('/posts', [new PostController(), 'index']);
 */

use App\Controllers\HomeController;
use App\Controllers\SubscribeController;

/** @var App\Core\Router $router */
$router->get('/', [new HomeController(), 'index']);
$router->post('/subscribe', [new SubscribeController(), 'store']);
