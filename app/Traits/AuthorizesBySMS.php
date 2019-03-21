<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Auth\PhoneOwner;


trait AuthorizesBySMS {

    public function sendAuthorizationToken(Request $request) {
        
        $this->validatePhone($request);
        
        $phone_user = new PhoneOwner($request->input('phone'));
        $phone_user->sendPasswordResetNotification($this->broker()->getRepository()->create($phone_user));
        
        return response()->json(['phone' => $request->input('phone'), 'status' => 1 ]);
    }
    
    public function validatePhone(Request $request) {
        $request->validate(['phone' => 'required|phone', 'g-recaptcha-response' => 'required|recaptcha']);
    }

    public function validateToken(Request $request) {
        $phone_user = new PhoneOwner($request->input('phone'));
        $tokens = $this->broker()->getRepository();
        Validator::make($request->all(), [
            'phone' => 'required|phone',
            'token' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($tokens, $phone_user) {
                    if(!$tokens->exists($phone_user, $value)) {
                       $fail($attribute.' is invalid.'); 
                    }
                }
            ]
        ])->validate();
        $tokens->delete($phone_user);
    }
    
    protected function broker()
    {
        return Password::broker();
    }
}

