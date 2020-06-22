<?php

namespace App\Interfaces;

use App\Models\Database\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Database\UserExt;
use App\Models\Database\ExtSource;

interface UserManager
{
    public function createUserWithContacts(UserExt $user_ext, array $data): User;
   
    public function findUser(array $data): Collection;
    
    public function updateUserWithContacts(User $user, UserExt $user_ext, array $data): User;
    
    public function mergeUserWithContacts(User $source, User $dest);
    
    public function removeAccount(User $user, ExtSource $source);
    
    public function getRequiredTrustLevel(User $user);
        
    public function validateCreate(User $user, $user_ext_data) : bool;
    
    public function validateUpdate(User $user, $user_ext_data) : bool;
    
    public function getValidData();

    public function getValidationErrors();
}

