<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use App\Notifications\SmsAuthorizationCode;

class PhoneUser implements CanResetPassword {

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
    
};

trait AuthorizesBySMS {

    public function sendAuthorizationToken(Request $request) {
        
        $this->validatePhone($request);
        
        $phone_user = new PhoneUser($request->input('phone'));
        $phone_user->sendPasswordResetNotification($this->broker()->getRepository()->create($phone_user));
        
        return response()->json(['phone' => $request->input('phone'), 'status' => 1 ]);
    }
    
    public function validatePhone(Request $request) {
        $request->validate(['phone' => 'required|phone', 'g-recaptcha-response' => 'required|recaptcha']);
    }

    public function validateToken(Request $request) {
        
    }
    
}

