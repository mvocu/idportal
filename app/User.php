<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Traits\RemembersPassword;
use App\Models\Database\Contact;
use App\Models\Ldap\LdapUser;
use Illuminate\Notifications\Notification;
use App\Notifications\SmsPasswordReset;
use App\Interfaces\UserManager;
use App\Interfaces\ContactManager;

class User extends LdapUser implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use  Authorizable, CanResetPassword, Notifiable,  RemembersPassword;

    protected $contact_mgr;
    protected $user_mgr;
    
    public function __construct(UserManager $user_mgr, ContactManager $contact_mgr) {
        $this->user_mgr = $user_mgr;
        $this->contact_mgr = $contact_mgr;
    }
    
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
    public function routeNotificationForSms(Notification $notification = null) {
        if($notification instanceof SmsPasswordReset) {
            $contact = $this->getTrustedContact(Contact::TYPE_PHONE);
            return $contact->phone;
        }
        return $this->getTelephoneNumber();
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::getEmailForPasswordReset()
     */
    public function getEmailForPasswordReset()
    {
        $contact = $this->getTrustedContact(Contact::TYPE_EMAIL);
        return $contact->email;
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::sendPasswordResetNotification()
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new SmsPasswordReset($token));
    }
    
    protected function getTrustedContact($type) {
        $user = $this->user_mgr->findUser(['identifier' => $this->getUniqueIdentifier()])->first();
        $contact = $this->contact_mgr->findTrustedContacts($user, $type)->first();
        return $contact;
    }
}
