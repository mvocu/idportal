<?php

return [
    'username' => env('GINIS_USERNAME', ''),
    'password' => env('GINIS_PASSWORD', ''),
    'endpoints' => [
        'gin' => __DIR__ . '/../resources/wsdl/Gin1.wsdl',
    ],
];