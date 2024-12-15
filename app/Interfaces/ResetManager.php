<?php
namespace App\Interfaces;

use App\Models\Ldap\User as LdapUser;

interface ResetManager
{
        public function getAvailableMethods(LdapUser $model);
        
        public function getMethodDescription($method = null);
        
        
}

