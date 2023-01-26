<?php
namespace App\Interfaces;

use App\Models\Cas\MfaPolicy;
use App\Models\Ldap\User;

interface MfaManager
{
    public function getPolicy(User $user);
    
    public function setPolicy(User $user, MfaPolicy $policy);
    
    public function getGauthCredentials(User $user);
    
    public function getWebAuthnDevices(User $user);
}

