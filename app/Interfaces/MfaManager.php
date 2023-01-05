<?php
namespace App\Interfaces;

use App\Models\User;

interface MfaManager
{
    public function getPolicy(User $user);
}

