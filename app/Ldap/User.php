<?php

namespace App\Ldap;

use LdapRecord\Models\FreeIPA\User as Model;
use STS\SocialiteAuth\Contracts\SocialiteAuthenticatable;

class User extends Model implements SocialiteAuthenticatable
{

	public function getSocialiteIdentifierName()
	{
    		return 'email';
	}
 
	public function getSocialiteIdentifier()
	{
    		return $this->email;
	}

}
