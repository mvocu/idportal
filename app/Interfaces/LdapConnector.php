<?php

namespace App\Interfaces;

use App\Models\Database\User;
use App\Models\Ldap\LdapUser;
use Illuminate\Support\Collection;

interface LdapConnector
{

    public function findUser(User $user);
    
    public function createUser(User $user) : LdapUser;
    
    public function updateUser(User $user);
    
    public function renameUser(User $user) : LdapUser;
    
    public function deleteUser(User $user) : bool;
    
    public function syncUsers(Collection $users);
    
    public function buildDN(User $user);
    
    public function changePassword(LdapUser $user, $password);
}

