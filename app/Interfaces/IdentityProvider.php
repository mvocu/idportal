<?php
namespace App\Interfaces;

use Illuminate\Contracts\Auth\Authenticatable;

interface IdentityProvider
{
        public function authenticate(array $params) : Authenticatable;
        
        public function validate($id_token, $ac_token);

        public function logout();
}

