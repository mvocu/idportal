<?php

namespace App\Interfaces;

use App\Models\Database\User;
use App\Models\Database\UserExt;

interface IdentityManager
{
    
        public function buildIdentityForUser(UserExt $user_ext);

        public function hasIdentity(array $data): bool;
}

