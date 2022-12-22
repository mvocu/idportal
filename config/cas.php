<?php

return [
  
    // GuzzleHttp client parameters
    'connection' => [
        'base_uri' => 'https://cas1.cuni.cz/cas/actuator',
        'username' => env('CAS_SERVER_USERNAME', 'cas'),
        'password' => env('CAS_SERVER_PASSWORD', 'changeit'),
    ],
    
];
