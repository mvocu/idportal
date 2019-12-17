<?php

namespace App\Interfaces;

use App\Models\Database\User;

interface ConsentManager
{
    public function isAllowed($object, $attr, $value) : bool;
    
    public function hasActiveConsent(User $user) : bool;
    
}

