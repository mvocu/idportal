<?php

namespace App\Models\Ldap;

use Adldap\Models\User;
use Illuminate\Support\Facades\Auth;
use Adldap\Laravel\Facades\Adldap;
use Illuminate\Contracts\Routing\UrlRoutable;

class LdapUser extends User implements UrlRoutable
{
    /*
     * This is neccessary when resolving objects from URL parameters, as resolution first creates empty object and 
     * then calls resolveRouteBinding on it.  
     */
    public function __construct($attrs = [], $builder = null) {
        if(empty($builder)) {
            $builder = Adldap::getFacadeRoot()->search()->select('*');
        }
        parent::__construct($attrs, $builder);
    }
    
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
    
    public function getAttributesAndTags($attr = null) 
    {
        $result = [];
        $attrs = $this->getAttributes();
        foreach($attrs as $key => $value) {
            if(is_numeric($key)) {
                continue;
            }
            if(strpos($key, ';') === false) {
                $name = $key;
                $tag = "";
            } else {
                $keys = explode(';', $key);
                $name = $keys[0];
                $tag = $keys[1];
            }
            if($attr == null || $name == $attr) {
                $result[$name][$tag] = $value;
            }
        }
        return ($attr == null) ? $result : (array_key_exists($attr, $result) ? $result[$attr] : [] );
    }

    public function getRouteKey()
    {
        return base64_encode($this->getDn());
    }
    
    public function getRouteKeyName()
    {
        return 'dn';
    }
    
    public function resolveRouteBinding($value)
    {
        return Auth::guard()->getProvider()->retrieveById(base64_decode($value));
    }
    
}

