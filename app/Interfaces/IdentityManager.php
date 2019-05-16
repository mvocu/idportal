<?php

namespace App\Interfaces;

use App\Models\Database\User;
use App\Models\Database\UserExt;

interface IdentityManager
{
    
        public function buildIdentityForUser(UserExt $user_ext);

        public function validateIdentity(array $data) : bool;
        
        public function validateEqualIdentity(User $user, $user_ext_data) : bool;
        
        public function mergeUser(User $source, User $dest);
            
}

