<?php
namespace App\Auth;

use Adldap\Laravel\Resolvers\UserResolver;
use App\Models\Database\Contact;
use Exception;

class LdapUserResolver extends UserResolver
{
    public function byId($identifier)
    {
        return $this->query()->findByDn($identifier);
    }
    
    /**
     * {@inheritDoc}
     * @see \Adldap\Laravel\Resolvers\UserResolver::byCredentials()
     */
    public function byCredentials(array $credentials = array())
    {
        if (empty($credentials)) {
            return;
        }
        
        if(null != $user = parent::byCredentials()) 
            return $user;
        
        $username = $credentials[$this->getLdapDiscoveryAttribute()];
        $query = $this->query();
        // normalize input through database model mutators
        $contact = new Contact();
        try {
            if (false === strpos($username, '@')) {
                $contact->setAttribute('phone', $username);
                $phone = $contact->phone;
                if(!empty($phone))
                    $query = $query->whereEquals('telephoneNumber', $phone);
            } else {
                $contact->setAttribute('mail', $username);
                $mail = $contact->mail;
                if(!empty($mail))
                    $query = $query->whereEquals('mail', $mail);
            }
        } catch (Exception $e) {
            return;
        }
        if(!empty($phone) || !empty($mail)) 
            return $query->first();
        
        return;
    }


}

