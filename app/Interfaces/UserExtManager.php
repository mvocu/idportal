<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;

interface UserExtManager
{
    public function getUserResource(UserExt $user_ext) : array;
  
    public function createUserWithAttributes(ExtSource $source, array $data) : UserExt;
    
}

