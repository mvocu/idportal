<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use App\User;
use App\Models\Database\UserExt;

class UserExtPolicy
{
    use HandlesAuthorization;
    
    public function view(User $user, UserExt $userext)
    {
        return true;
    }
}

