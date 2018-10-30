<?php
namespace App\Auth;

use Adldap\Laravel\Resolvers\UserResolver;
use App\Models\Database\Contact;

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
        // normalize input through database model mutators
        $contact = new Contact(['mail' => $username, 'phone' => $username]);
        $mail = $contact->mail;
        $phone = $contact->phone;
        $query = $this->query();
        if(!empty($phone)) 
            $query = $query->orWhereEquals('telephoneNumber', $phone);
        if(!empty($mail)) 
            $query = $query->orWhereEquals('mail', $mail);
        if(!empty($phone) || !empty($mail)) 
            return $query->first();
        
        return;
    }


}

