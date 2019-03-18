<?php

namespace App\Traits;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ActivateUserNotification;

class ActivateUser implements CanResetPassword
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
        $this->notify(new ActivateUserNotification($this->email, $token));
    }
    
    
}


trait SendsAccountActivationEmail {

    public function showActivationForm()
    {
        return view('auth.activation');
    }

    public function sendActivationLink(Request $request)
    {
        $user = new ActivateUser($request->input('email'));
        $user->sendPasswordResetNotification($this->broker()->getRepository()->create($user));
    }
    
    public function validateToken(Request $request) {
        $activate_user = new ActivateUser($request->input('uid'));
        $tokens = $this->broker()->getRepository();
        Validator::make($request->all(), [
            'uid' => 'required|email',
            'token' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($tokens, $activate_user) {
                    if(!$tokens->exists($activate_user, $value)) {
                        $fail($attribute.' is invalid.');
                    }
                }
                ]
                ])->validate();
    }
    
}

