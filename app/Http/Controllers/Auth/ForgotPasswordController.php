<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\ExtSourceManager;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected $ext_source_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExtSourceManager $ext_source_mgr)
    {
        $this->middleware('guest');
        $this->ext_source_mgr = $ext_source_mgr;
    }
    
    /**
     * @Override
     * 
     * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
     */
    public function showLinkRequestForm(Request $request)
    {
        if($request->input('auto')) {
            return view('auth.passwords.autosend', $request->only('phone'));
        }
        return view('auth.passwords.send', [ 'idp' => $this->ext_source_mgr->listAuthenticators()->pluck('name') ]);
    }

    public function sendResetCode(Request $request) {
        
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only(['uid', 'preferred'])
            );
        
        return starts_with($response, Password::RESET_LINK_SENT) 
        ? $this->sendResetLinkResponse($request, $response)
        : $this->sendResetLinkFailedResponse($request, $response);
    }
    
    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return redirect()->route('password.token')
            ->withInput($request->only('uid'))
            ->with('status', trans($response));
    }
    
    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return back()
        ->withInput($request->only('uid'))
        ->withErrors(['failure' => trans($response)]);
    }
    
}
