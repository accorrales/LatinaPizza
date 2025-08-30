<?php

// config/kitchen.php
return [
    // minutos por defecto si no hay match
    'default_sla' => 25,

    // puedes afinar por tipo de pedido
    'sla_by_tipo' => [
        'pickup'  => 20,
        'express' => 35,
    ],
];