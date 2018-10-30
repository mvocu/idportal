<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Traits\RemembersPassword;
use App\Models\Ldap\LdapUser;
use Illuminate\Notifications\Notification;
use App\Notifications\SmsPasswordReset;

class User extends LdapUser implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use  Authorizable, CanResetPassword, Notifiable,  RemembersPassword;

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\Authenticatable::getAuthPassword()
     */
    public function getAuthPassword()
    {
        return $this->password;
    }
    
    public function __get($key) {
        switch($key) {
            case 'name':
                return $this->getCommonName();
                
            default:
                return parent::__get($key);
        }
    }
    
    /**
     * 
     * @param Notification $notification
     * @return string
     */
    public function routeNotificationForSms(Notification $notification) {
        return $this->getTelephoneNumber();
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::getEmailForPasswordReset()
     */
    public function getEmailForPasswordReset()
    {
        return $this->getUniqueIdentifier();
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::sendPasswordResetNotification()
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new SmsPasswordReset($token));
    }
    
}
