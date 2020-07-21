<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function resetpw(User $user) 
    {
        $manager = $user->manager;
        return is_null($manager);
    }
    
    public function changepw(User $current, User $subject)
    {
        
    }

}
