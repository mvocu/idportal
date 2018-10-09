<?php

namespace App\Auth;

use Adldap\Laravel\Auth\NoDatabaseUserProvider;
use App\User;

class LdapUserProvider extends NoDatabaseUserProvider
{
    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveByCredentials()
     */
    public function retrieveByCredentials(array $credentials)
    {
        $ldapuser = parent::retrieveByCredentials($credentials);
        $user = new User($ldapuser->getAttributes(), $ldapuser->getQuery());
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveById()
     */
    public function retrieveById($identifier)
    {
        // this proxies to the Resolver::byId(), so this'd rather be the correct one
        $ldapuser = parent::retrieveById($identifier);
        $user = new User($ldapuser->getAttributes(), $ldapuser->getQuery());
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveByToken()
     */
    public function retrieveByToken($identifier, $token)
    {
        // TODO Auto-generated method stub
        $ldapuser = parent::retrieveByToken($identifier, $token);
        $user = new User($ldapuser->getAttributes(), $ldapuser->getQuery());
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::updateRememberToken()
     */
    public function updateRememberToken(\Illuminate\Contracts\Auth\Authenticatable $user, $token)
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::validateCredentials()
     */
    public function validateCredentials(\Illuminate\Contracts\Auth\Authenticatable $user, array $credentials)
    {
        if(parent::validateCredentials($user, $credentials)) {
            if(method_exists($user, "rememberPassword") && !empty($credentials['password'])) {
                $user->rememberPassword($credentials['password']);
            }
            return true;
        } else {
            return false;
        }
    }

    
}

