<?php

namespace App\Interfaces;

use App\Models\Database\User;

interface ConsentManager
{
    public function isAllowed($object, $attr, $value) : bool;
    
    public function hasActiveConsent(User $user) : bool;
    
    public function hasDeniedConsent(User $user) : bool;
    
    public function setConsent(User $user, $active);
    
    public function setConsentRequested(User $user, $active);
    
    public function getExpiryDate(User $user);
    
    public function expiresSoon(User $user);
}

