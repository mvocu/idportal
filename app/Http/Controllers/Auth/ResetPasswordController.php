<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Support\MessageBag;
use App\Interfaces\LdapConnector;
use App\Models\Database\ExtSource;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    protected $ldap_mgr;
    
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LdapConnector $ldap_mgr)
    {
        $this->ldap_mgr = $ldap_mgr;
        
        $this->middleware('guest')->except('resetExtIdp');
        $this->middleware('eidp')->only(['showExtIdpForm']);
        $this->middleware('auth.eidp')->only('resetExtIdp');
    }
    
    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'uid' => $request->uid]
            );
    }
    
    public function showExtIdpForm(Request $request, $client)
    {
        $eidp_user = Auth::guard($client)->user();
        $idp_s = $this->getExtSource($client);
        $user = $this->ldap_mgr->findUserByExtSource($idp_s, $eidp_user->getAuthIdentifier());
        if(is_null($user)) {
            return redirect()->route('password.request')
                ->withErrors(['failure' => __('No user found for given external identity')]);
        }
        return view('auth.passwords.reset')->with(
            ['token' => $eidp_user->getRememberToken(), 'uid' => $user->getFirstAttribute('uid'), 'client' => $client]
            );
    }
    
    public function resetExtIdp(Request $request, $client)
    {
        // $oidc_user = Auth::guard($client)->user();
        // $idp_s = $this->getExtSource($client);
        // $auth_user = $this->ldap_mgr->findUserByExtSource($idp_s, $oidc_user->getAuthIdentifier());
        $auth_user = Auth::user();
        $this->validate($request, $this->rules(), $this->validationErrorMessages());
        $credentials = $this->credentials($request);
        try {
            $this->broker()->validateNewPassword($credentials);
            $user = $this->broker()->getUser($credentials);
            if($user->getAuthIdentifier() != $auth_user->getAuthIdentifier()) {
                return $this->sendResetFailedResponse($request, "Authenticated user does not match the target");
            }
            $this->resetPassword($user, $credentials['password']);
        } catch (\Exception $e) {
            return $this->sendResetFailedResponse($request, $e->getMessage());
        }
        return $this->sendResetResponse("passwords.reset");
    }
    
    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'uid' => 'required|string',
            'password' => 'required|confirmed|min:6',
        ];
    }
    
    protected function credentials(Request $request)
    {
        return $request->only(
            'uid', 'password', 'password_confirmation', 'token'
            );
    }
    
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->ldap_mgr->setUserLock($user, false);
        
        $this->ldap_mgr->changePassword($user, $password);
        
        $user->rememberPassword($password);
        
        event(new PasswordReset($user));
        
        $this->guard()->login($user);
    }
    
    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        if($response instanceof MessageBag) {
            return redirect()->back()
            ->withInput($request->only('uid'))
            ->withErrors($response);
        } else {
            return redirect()->back()
            ->withInput($request->only('uid'))
            ->withErrors(['failure' => __($response)]);
        }
    }
    
    protected function getExtSource($client)
    {
        return ExtSource::where([
            ['name', '=', $client],
            ['identity_provider', '=', 1]
        ])->get()->first();
    }
    
}
