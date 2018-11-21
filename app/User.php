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
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\Database\User as DbUser;

class User extends LdapUser implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authorizable, Notifiable, RemembersPassword;
    use CanResetPassword { sendPasswordResetNotification as sendPasswordResetNotificationMail; }

    protected $db_user;
    
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

    public function routeNotificationForMail(Notification $notification = null) {
        if($notification instanceof ResetPassword) {
            $contact = $this->getTrustedContact(Contact::TYPE_EMAIL);
            return $contact->email;
        }
        return $this->getEmail();
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::getEmailForPasswordReset()
     */
    public function getEmailForPasswordReset()
    {
        // this is not the address to send the notification to - it serves just as key to the token table
        return $this->getUniqueIdentifier();
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::sendPasswordResetNotification()
     */
    public function sendPasswordResetNotification($token)
    {
        if($this->getPasswordResetContactType($this) == Contact::TYPE_PHONE) {
            $this->notify(new SmsPasswordReset($token));
            return "sms";
        } else {
            $this->sendPasswordResetNotificationMail($token);
            return "mail";
        }
    }
    
    public function getDatabaseUser() {
        if(empty($this->db_user)) {
            $this->db_user = DbUser::where('identifier', '=', $this->getUniqueIdentifier())->first();
        }
        return $this->db_user;
    }
    
    public function getTrustedContact($type) {
        $contact_mgr = resolve('App\Interfaces\ContactManager');
        $user = $this->getDatabaseUser();
        if(empty($user)) {
            if($type == Contact::TYPE_PHONE) {
                $phone = $this->getTelephoneNumber();
                if(empty($phone)) {
                    return null;
                }
                $contact = new Contact(['type' => $type, 'phone' => $phone]);
            } elseif($type == Contact::TYPE_EMAIL) {
                $email = $this->getEmail();
                if(empty($email)) {
                    return null;
                }
                $contact = new Contact(['type' => $type, 'email' => $email]);
            }
        } else {
            $contact = $contact_mgr->findTrustedContacts($this->getDatabaseUser(), $type)->first();
        }
        return $contact;
    }
    
    public function getPasswordResetContactType(User $user) {
        $contact = $user->getTrustedContact(Contact::TYPE_PHONE);
        if(!empty($contact)) {
            return Contact::TYPE_PHONE;
        } else {
            return Contact::TYPE_EMAIL;
        }
    }
    
}
