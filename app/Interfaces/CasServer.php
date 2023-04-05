<?php
namespace App\Interfaces;

interface CasServer
{
    public function request($method, $uri);
    
    public function getGauthCredentials($id);
    
    public function getWebAuthnDevices($id);
    
    public function getTrustedDevices($id);
    
    public function deleteTrustedDevice($key);
    
}
