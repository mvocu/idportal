<?php

return [
    'username' => env('GINIS_USERNAME', ''),
    'password' => env('GINIS_PASSWORD', ''),
    'endpoints' => [
        'ddp' => 'http://ginis/Gordic/Ginis/Ws/test/POR/DDP01/ddp.asmx',
        'adm' => 'http://ginis/Gordic/Ginis/Ws/test/POR/ADM01/Adm.asmx',
        'gin' => 'http://ginis/Gordic/Ginis/Ws/test/POR/GIN01/Gin.asmx',
    ],
];