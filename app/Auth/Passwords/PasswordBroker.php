<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordBroker as Broker;
use Closure;
use App\User;
use Illuminate\Support\MessageBag;

class PasswordBroker extends Broker
{
    const RESET_LINK_SENT_SMS = "passwords.sent.sms";
    const INVALID_CONTACT = "passwords.nomail";
    const NOT_ALLOWED = "passwords.not_allowed";
    
    /**
     * {@inheritDoc}
     * @see \Illuminate\Auth\Passwords\PasswordBroker::reset()
     */
    public function reset(array $credentials, Closure $callback)
    {
        try {
            $result = parent::reset($credentials, $callback);
        } catch (\Exception $exc) {
            return new MessageBag([ 'failure' => $exc->getMessage() ]);
        }
    }

    /**
     * {@inheritDoc}
     * @see \Illuminate\Auth\Passwords\PasswordBroker::sendResetLink()
     */
    public function sendResetLink(array $credentials)
    {
        if(array_key_exists('preferred', $credentials)) {
            $preferred = $credentials['preferred'];
            unset($credentials['preferred']);
        }
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);
        
        if (is_null($user)) {
            return static::INVALID_USER;
        }

        if ($user->cant('resetpw', User::class)) {
            return static::NOT_ALLOWED;
        }
        
        if(!empty($preferred)) {
            $user->setPreferredPasswordResetMethod($preferred);
        }
        
        // Once we have the reset token, we are ready to send the message out to this
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        $result = $user->sendPasswordResetNotification(
            $this->tokens->create($user)
            );

        if($result == "sms") {
            return static::RESET_LINK_SENT_SMS;
        } elseif($result == "mail") {
            return static::RESET_LINK_SENT;
        } else {
            return static::INVALID_CONTACT;
        }
    }

    
}

