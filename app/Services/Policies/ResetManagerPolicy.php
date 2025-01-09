<?php

namespace App\Services\Policies;

use App\Models\Ldap\User;
use App\Services\ResetManager;

class ResetManagerPolicy
{

    public function passwordResetUsingNia(User $user = null, ResetManager $model) {
        return true;        
    }
    
    public function passwordResetUsingSvipeid(User $user = null, ResetManager $model) {
        return true;
    }

    public function passwordResetUsingEduid(User $user = null, ResetManager $model) {
        return true;
    }
    
    public function passwordResetUsingEdugain(User $user = null, ResetManager $model) {
        return true;
    }

    public function passwordResetUsingSmsChallenge(User $user = null, ResetManager $model) {
        return true;
    }

    public function passwordResetUsingMailChallenge(User $user = null, ResetManager $model) {
        return true;
    }
    
}
