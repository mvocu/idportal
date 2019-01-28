<?php

namespace App\Interfaces;

use App\Http\Resources\UserResource;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;

interface UserExtManager
{
    public function getUserResource(UserExt $user_ext) : UserResource;
  
    public function createUserWithAttributes(ExtSource $source, array $data) : UserExt;
    
}

