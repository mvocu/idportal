<?php
namespace App\Interfaces;

use App\Models\Database\UserExt;
use App\Models\Database\User;

interface IdentityExtractor
{
        public function buildUserAndContacts(UserExt $extuser) : User;
}

