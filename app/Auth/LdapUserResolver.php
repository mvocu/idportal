<?php
namespace App\Auth;

use Adldap\Laravel\Resolvers\UserResolver;

class LdapUserResolver extends UserResolver
{
    public function byId($identifier)
    {
        return $this->query()->findByDn($identifier);
    }

}

