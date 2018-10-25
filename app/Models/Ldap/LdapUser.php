<?php

namespace App\Models\Ldap;

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

    public function setPassword($password)
    {
        $this->validateSecureConnection();
        
        $mod = $this->newBatchModification(
            $this->schema->userPassword(),
            LDAP_MODIFY_BATCH_REPLACE,
            $password
            );
        
        return $this->addModification($mod);
    }
    
    

}

