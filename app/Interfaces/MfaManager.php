<?php
namespace App\Interfaces;

use App\Models\Ldap\User;

interface MfaManager
{
    public function getPolicy(User $user);
    
    public function getGauthCredentials(User $user);
    
    public function getWebAuthnDevices(User $user);
}

