<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\ExtSourceManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Interfaces\LdapConnector;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use App\Traits\FindsExternalAccount;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, FindsExternalAccount;

    protected $ext_source_mgr;
    
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExtSourceManager $ext_source_mgr, LdapConnector $ldap_mgr)
    {
        $this->middleware('guest')->except(['logout', 'loginExtIdp']);
        $this->middleware('eidp')->only('loginExtIdp');
        $this->ext_source_mgr = $ext_source_mgr;
        $this->ldap_mgr = $ldap_mgr;
    }

    /**
     * @Override
     *
     */
    public function showLoginForm()
    {
        return view('auth.login', [ 'idp' => $this->ext_source_mgr->listAuthenticators()->pluck('name') ]);
    }
    
    public function loginExtIdp(Request $request, $client)
    {
        $auth_user = $this->findExternalAccount(Auth::guard($client)->user(), $client);
        if(is_null($auth_user)) {
            return redirect()->back()
                ->withErrors(['failure' => __('External identity is not registered.')]);
        }
        return redirect()->intended($this->redirectPath());
    }
    
    /**
     * @Override
     *
     */
    public function username() {
	    return 'uid';
    }
    
}
