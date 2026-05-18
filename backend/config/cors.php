<?php

// config/cors.php
// Replace the default Laravel cors.php with this file.

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    | Local dev: Next.js runs on localhost:3000
    | Production: swap to your real domain in .env or directly here
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        // Production — add when you go live:
        // 'https://yourdomain.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'Accept',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 86400, // 24 hours — browser caches the preflight response

    /*
    | false because we use token-based auth (Bearer), not cookies.
    | Set to true only if you switch to Sanctum stateful / cookie auth.
    */
    'supports_credentials' => false,

];