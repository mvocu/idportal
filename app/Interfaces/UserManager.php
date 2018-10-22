<?php

namespace App\Interfaces;

use App\Models\Database\User;
use Illuminate\Database\Eloquent\Collection;

interface UserManager
{
    public function createUserWithContacts(array $data): User;
   
    public function findUser(array $data): Collection;
}

