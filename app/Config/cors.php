<?php

return [
    // Empty by default = CORS handling is a no-op (same-origin app, nothing
    // to allow). Add origins only when something else actually needs to
    // call this app cross-origin, e.g. ['https://app.example.com'] or ['*'].
    'allowed_origins' => [],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'X-CSRF-Token'],
    'allow_credentials' => false,
];
