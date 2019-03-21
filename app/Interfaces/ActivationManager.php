<?php

namespace App\Interfaces;

use Illuminate\Contracts\Auth\CanResetPassword;
use App\Models\Database\UserExt;

interface ActivationManager
{
    public function sendActivationLink(CanResetPassword $user);

    public function validateToken(CanResetPassword $user, $data);

    public function activateAccount(CanResetPassword $user);
}

