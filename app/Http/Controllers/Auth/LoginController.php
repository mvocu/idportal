<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\ExtSourceManager;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Interfaces\LdapConnector;

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

    use AuthenticatesUsers;

    protected $ext_source_mgr;
    protected $ldap_mgr;
    
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
        $this->middleware('guest')->except(['logout', 'loginOidc']);
        $this->middleware('oidc')->only('loginOidc');
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
    
    public function loginOidc(Request $request, $client)
    {
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
