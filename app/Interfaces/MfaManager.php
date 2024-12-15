<?php
namespace App\Interfaces;

use App\Models\Cas\MfaPolicy;
use App\Models\Ldap\User;
use App\Models\Cas\GauthRecord;

interface MfaManager
{
    public function getPolicy(User $user);
    
    public function setPolicy(User $user, MfaPolicy $policy);
    
    public function getGauthCredentials(User $user);
    
    public function importGauthCredentials(User $user, GauthRecord $gauth);
	
	public function exportGauthCredentials(User $user);
    
    public function getPagerNumber(User $user);
    
    public function deleteGauthCredentials(User $user, $id = null);
    
    public function getWebAuthnDevices(User $user);
    
    public function deleteWebAuthnDevices(User $user, $id = null);
    
    public function getTrustedDevices(User $user);
    
    public function deleteTrustedDevices(User $user, $id = null);
}

