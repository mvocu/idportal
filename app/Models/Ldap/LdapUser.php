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

    public function getUniqueIdentifier() 
    {
        return $this->getFirstAttribute($this->schema->uniqueIdentifier());    
    }
    
    public function setPassword($password)
    {
        $this->validateSecureConnection();
        
        $mod = $this->newBatchModification(
            $this->schema->userPassword(),
            LDAP_MODIFY_BATCH_REPLACE,
            [ $password ]
            );
        
        return $this->addModification($mod);
    }
    
    public function addRole($name) 
    {
        $current = $this->getAttribute('nsroledn');
        if(!isset($current)) {
            $current = [];
        }
        if(!in_array($name, $current)) {
            $current[] = $name;
            $this->setAttribute('nsroledn', $current);
        }
        return $this;
    }
    
    public function removeRole($name)
    {
        $current = $this->getAttribute('nsroledn');
        if(!isset($current)) {
            $current = [];
        }
        if(in_array($name, $current)) {
            $current = array_diff($current, [ $name ]);
            $this->setAttribute('nsroledn', $current);
        }
        return $this;
    }
    
}

