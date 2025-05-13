<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VotingUserNotification;

class VotingUser implements CanResetPassword
{
    
    use Notifiable;
    
    // this is used to route notifications through mail channel
    protected $email;
    
    public function __construct($email)
    {
        $this->email = $email;
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::getEmailForPasswordReset()
     */
    public function getEmailForPasswordReset()
    {
        // used as key for tokens table
        return $this->email;
    }
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Contracts\Auth\CanResetPassword::sendPasswordResetNotification()
     */
    public function sendPasswordResetNotification($token)
    {
        // create and send notification
        $this->notify(new VotingUserNotification($this->email, $token));
    }
    
    
}
