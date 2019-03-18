<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Traits\ActivatesAccount;
use App\Traits\SendsAccountActivationEmail;

class ActivateController extends Controller
{
    use SendsAccountActivationEmail;
    
    use ActivatesAccount;

    use ResetsPasswords;     /* PROVIDES: showResetForm, reset, rules, validationErrorMessage, credentials, resetPassword,
                               sendResetResponse, sendResetFailedResponse, broker, guard */
    
    
    /**
     * Where to redirect users after activation.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

}

