<?php

$defaultOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
    'http://localhost:5174',
    'http://127.0.0.1:5174',
    'http://192.168.1.19:8000',
    'http://192.168.1.19:5173',
    'http://192.168.1.19:5174',
    'https://carsteamunlimited.com',
    'https://www.carsteamunlimited.com',
    'https://ctu-admin.vercel.app',
];

$envOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
)));

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'admin/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_merge($defaultOrigins, $envOrigins))),

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
        '#^https://(www\.)?carsteamunlimited\.com$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
