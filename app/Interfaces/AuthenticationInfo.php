<?php
namespace App\Interfaces;

interface AuthenticationInfo
{
    
    public function getAuthMethod();
    
    public function getLevelOfAuthority();
    
    public function getRemoteClient();

    public function getRemoteAuthenticationMethod();
}

