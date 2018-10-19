<?php

namespace App\Interfaces;

use App\Models\Database\User;

interface UserManager
{
    public function createUserWithContacts(array $data): User;
   
}

