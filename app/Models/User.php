<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable;
use App\Interfaces\AuthenticationInfo;
use App\Models\Ldap\User as LdapUser;
use App\Traits\HasAuthUser;
use App\Traits\HasLdapUser;
use App\Auth\OidcUser;

class User implements Authenticatable, AuthenticationInfo, Authorizable
{
    use  Notifiable, HasAuthUser, HasLdapUser;
    
    public function __construct(Authenticatable $auth_user, LdapUser $model)
    {
        $this->auth_user = $auth_user;
        $this->ldap_user = $model;
    }
    
    public function getDisplayName()
    {
        if(!is_null($this->ldap_user)) {
            return $this->ldap_user->getFirstAttribute('cn');
        }
        
        if($this->auth_user instanceof OidcUser) {
            // TODO move this into OidcUser?
            if(!empty($this->auth_user->info['attributes']['name'])) {
                return $this->auth_user->info['attributes']['name'];
            }
                
            return $this->auth_user->claims['sub'];
        }
    }
    
    public function can($abilities, $arguments = [])
    {
        return false;
    }

}
