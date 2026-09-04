<?php

return [
    'app_name' => getenv('APP_NAME') ?: 'VinPHP',
    'base_path' => '', // set to '/subfolder' if not served from domain root
    'env' => getenv('APP_ENV') ?: 'development',
];
