<?php

namespace App\Models;

use Adldap\Models\User;

class LdapUser extends User
{
    /**
     * {@inheritDoc}
     * @see \Adldap\Models\User::getAuthIdentifier()
     */
    public function getAuthIdentifier()
    {
        return $this->getDistinguishedName();
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Models\User::getAuthIdentifierName()
     */
    public function getAuthIdentifierName()
    {
        return $this->schema->distinguishedName();
    }

    /**
     * {@inheritDoc}
     * @see \Adldap\Models\User::getAuthPassword()
     */
    public function getAuthPassword()
    {
 
    }



}

