<?php

namespace App\Auth;

use Adldap\Laravel\Auth\NoDatabaseUserProvider;

class LdapUserProvider extends NoDatabaseUserProvider
{
    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveByCredentials()
     */
    public function retrieveByCredentials(array $credentials)
    {
        // TODO Auto-generated method stub
        return parent::retrieveByCredentials($credentials);
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveById()
     */
    public function retrieveById($identifier)
    {
        // this proxies to the Resolver::byId(), so this'd rather be the correct one
        return parent::retrieveById($identifier);        
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Auth\NoDatabaseUserProvider::retrieveByToken()
     */
    public function retrieveByToken($identifier, $token)
    {
        // TODO Auto-generated method stub
        return parent::retrieveByToken($identifier, $token);
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
        // TODO Auto-generated method stub
        return parent::validateCredentials($user, $credentials);
    }

    
}

