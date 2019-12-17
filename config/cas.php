<?php

return [
    'login_url' => 'https://' . env('CAS_SERVER', 'cas.server.name') . '/cas/login',
    'logout_url' => 'https://' . env('CAS_SERVER', 'cas.server.name') . '/cas/logout'
];