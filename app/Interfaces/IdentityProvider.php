<?php
namespace App\Interfaces;

use Illuminate\Contracts\Auth\Authenticatable;

interface IdentityProvider
{
        public function authenticate(array $params);
        
        public function validate($id_token, $ac_token);
        
        public function introspect($ac_token);

        public function logout($id_tokent, $redirect);
        
        public function getLastError();
}

