<?php

namespace App\Auth;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Auth\CanResetPassword;
use App\Notifications\SmsAuthorizationCode;

class PhoneOwner implements CanResetPassword
{
    
    use Notifiable;
    
    protected $phone;
    
    public function __construct($phone){
        $this->phone = $phone;
    }
    
    // return identifier in token database
    public function getEmailForPasswordReset()
    {
        return $this->phone;
    }
    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new SmsAuthorizationCode($token));
    }
    
    // return destination for SMS channel
    public function routeNotificationForSms(Notification $notification = null) {
        return $this->phone;
    }
    
    
}

