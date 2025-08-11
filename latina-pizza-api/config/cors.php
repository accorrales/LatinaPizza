<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie', // ← importante si usás autenticación con cookies
        'login',
        'logout',
        'register',
        'guardar-tipo-pedido', // ← ruta personalizada para guardar tipo de pedido
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
        'https://latinapizza.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ← necesario para cookies o sesión

];


