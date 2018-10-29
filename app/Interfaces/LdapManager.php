<?php

namespace App\Interfaces;

use App\Models\Database\User;
use App\Models\Ldap\LdapUser;

interface LdapManager
{

    public function createUser(User $user) : LdapUser;
    
    public function updateUser(User $user) : LdapUser;
    
    public function renameUser(User $user) : LdapUser;
    
    public function deleteUser(User $user) : bool;
    
    public function buildDN(User $user);
    
    public function changePassword(LdapUser $user, $password);
}

