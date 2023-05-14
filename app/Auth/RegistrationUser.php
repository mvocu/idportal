<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\CanResetPassword;

class RegistrationUser implements CanResetPassword
{
    protected $id;
    
    public function __construct($id) {
        $this->id = $id;
    }
    
    public function getEmailForPasswordReset()
    {
        return $this->id;
    }

    public function sendPasswordResetNotification($token)
    {
        return true;
    }
}

