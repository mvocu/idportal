<?php
namespace App\Interfaces;

use App\Models\Ldap\User;

interface UserExtManager
{
    public function findUserByExtIdentity($id);
    
    public function listExternalIdentities(User $user);
    
    public function setExtIdentity(User $user, $provider, $id);
    
    public function removeExtIdentity(User $user, $provider);
}

