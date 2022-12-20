<?php
namespace App\Traits;

use App\Models\Ldap\User as LdapUser;

trait HasLdapUser
{
    protected LdapUser $ldap_user;

    public function getLdapUser()
    {
        return $this->ldap_user;
    }
    
    public function __get($name)
    {
        return $this->ldap_user->getAttribute($name);
    }
    
}

