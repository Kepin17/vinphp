<?php

namespace App\Core;

/**
 * Inert by default: app/Config/cors.php's allowed_origins is empty until
 * you configure it, so a same-origin app pays nothing for this. Call
 * handle() once, early, before routing — it also short-circuits an OPTIONS
 * preflight, which Router has no route for and would otherwise 404.
 */
class Cors
{
    public static function handle(): void
    {
        $config = require ROOT_PATH . '/app/Config/cors.php';
        $origins = $config['allowed_origins'];

        if (!$origins) {
            return;
        }

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;

        if ($origin !== null && (in_array('*', $origins, true) || in_array($origin, $origins, true))) {
            header("Access-Control-Allow-Origin: {$origin}");
            header('Access-Control-Allow-Methods: ' . implode(', ', $config['allowed_methods']));
            header('Access-Control-Allow-Headers: ' . implode(', ', $config['allowed_headers']));

            if ($config['allow_credentials']) {
                header('Access-Control-Allow-Credentials: true');
            }
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
