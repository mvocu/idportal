<?php
namespace App\Interfaces;

use App\Models\Ldap\User;
use App\Auth\OidcUser;

interface AuthenticationManager
{
    public function findUserByExtIdentity($id);
    
    public function listExternalIdentities(User $user);
    
    public function setExtIdentity(User $user, $client, $provider, $id);
    
    public function removeExtIdentity(User $user, $client, $provider);
    
    public function getIdentity(OidcUser $user);
    
    public function getRemoteProvider(OidcUser $user);
    
    public function isEligibleForRegistration(OidcUser $user);
    
    public function hasAuthentication(User $user);
    
}

