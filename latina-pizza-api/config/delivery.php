<?php

return [
    // Cobertura máxima (km). Fuera de esto: no disponible
    'max_km' => 10,

    // Tramos de precio por distancia (km => monto). Inclusive en el límite inferior.
    // Ejemplo en colones: 0–3 km ₡0, >3–6 km ₡1000, >6–10 km ₡2000
    'tiers' => [
        ['max' => 1,  'fee' => 600],
        ['max' => 2,  'fee' => 1000],
        ['max' => 3,  'fee' => 1500],
        ['max' => 4, 'fee' => 2000],
        ['max' => 4.5,  'fee' => 2500],
        ['max' => 5, 'fee' => 3000],
        ['max' => 6, 'fee' => 3500],
        ['max' => 7, 'fee' => 4000],
        ['max' => 8, 'fee' => 4500],
        ['max' => 9, 'fee' => 5000],
        ['max' => 10, 'fee' => 5500],
    ],

    // Moneda para mostrar
    'currency' => '₡',
];
