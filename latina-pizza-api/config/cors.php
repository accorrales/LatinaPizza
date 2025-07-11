<?php

return [

    'paths' => ['api/*'], // aplica solo a rutas de la API

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',   // Frontend con Vite o React
        'http://localhost:8000',   // Laravel frontend (Blade o Breeze)
        'http://127.0.0.1:8000',
        'http://127.0.0.1:3000',
        'https://latinapizza.com', // Producción real
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // importante si usás cookies/session
];

