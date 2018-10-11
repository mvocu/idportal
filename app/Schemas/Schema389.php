<?php

namespace App\Schemas;

use Adldap\Schemas\BaseSchema;
use App\Models\Ldap\LdapUser;

class Schema389 extends BaseSchema
{

    /**
     * {@inheritdoc}
     */

    public function distinguishedName()
    {
        return 'dn';
    }
    
    /**
     * {@inheritdoc}
     */
    public function distinguishedNameSubKey()
    {
        //
    }
    
    /**
     * {@inheritdoc}
     */
    public function filterEnabled()
    {
        return sprintf('(!(%s=*))', $this->lockoutTime());
    }
    
    /**
     * {@inheritdoc}
     */
    public function filterDisabled()
    {
        return sprintf('(%s=*)', $this->lockoutTime());
    }
    
    /**
     * {@inheritdoc}
     */
    public function lockoutTime()
    {
        return 'accountunlocktime';
    }
    
    /**
     * {@inheritdoc}
     */
    public function objectCategory()
    {
        return 'objectclass';
    }
    
    /**
     * {@inheritdoc}
     */
    public function objectClassGroup()
    {
        return 'groupofnames';
    }
    
    /**
     * {@inheritdoc}
     */
    public function objectClassOu()
    {
        return 'groupofuniquenames';
    }
    
    /**
     * {@inheritDoc}
     * @see \Adldap\Schemas\BaseSchema::userModel()
     */
    public function userModel()
    {
        return LdapUser::class;
    }

    /**
     * {@inheritdoc}
     */
    public function objectClassPerson()
    {
        return 'person';
    }
    
    /**
     * {@inheritdoc}
     */
    public function objectGuid()
    {
        return 'nsuniqueuid';
    }
    
    /**
     * {@inheritdoc}
     */
    public function objectGuidRequiresConversion()
    {
        return false;
    }

}

