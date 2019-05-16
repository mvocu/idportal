<?php

namespace App\Interfaces;

use App\Models\Database\User;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Database\UserExt;

interface UserManager
{
    public function createUserWithContacts(UserExt $user_ext, array $data): User;
   
    public function findUser(array $data): Collection;
    
    public function updateUserWithContacts(User $user, UserExt $user_ext, array $data): User;
    
    public function mergeUserWithContacts(User $source, User $dest);
    
    public function getRequiredTrustLevel(User $user);

    public function validateUpdate(User $user, $user_ext_data) : bool;
    
    public function getValidData();

    public function getValidationErrors();
}

