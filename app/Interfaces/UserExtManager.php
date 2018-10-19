<?php

namespace App\Interfaces;

use App\Models\Database\UserExt;

interface UserExtManager
{
    public function extractUserWithAttributes(UserExt $user_ext) : array;
  
}

