<?php

namespace App\Http\Controllers;

use App\Interfaces\ConsentManager;
use App\Interfaces\LdapConnector;
use App\Interfaces\UserManager;
use App\Models\Database\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    private $user_mgr;
    
    private $consent_mgr;
    
    private $ldap_mgr;
    
    public function __construct(
        UserManager $user_mgr,
        ConsentManager $consent_mgr,
        LdapConnector $ldap_mgr)
    {
        $this->user_mgr = $user_mgr;
        $this->consent_mgr = $consent_mgr;
        $this->ldap_mgr = $ldap_mgr;
    }
    
    public function status(Request $request)
    {
        if(Auth::check()) {
            return redirect(route('account.status.show', ['user' => Auth::user()->getDatabaseUser() ]));
        }
        return view('searchaccount');
    }
    
    public function search(Request $request) 
    {
        $login = $request->input('uid');
        $users = $this->ldap_mgr->findUserByCredentials($login);
        if($users->isEmpty()) {
            return back()->withErrors(['failure' => __('No account found. You can register new account with this identifier.')]);
        } elseif ($users->count() > 1) {
            return back()->withErrors(['failure' => __('More than one account found.')]);
        }
        $user = $this->user_mgr->findUser(['identifier' => $users->first()->getFirstAttribute('uniqueidentifier')])->first();
        return redirect(route('account.status.show', ['user' => $user]));
    }
    
    public function show(Request $request, User $user) {
        $ldap_user = $this->ldap_mgr->findUser($user);
        $lock = $this->ldap_mgr->isUserLocked($ldap_user);
        $pwlock = $this->ldap_mgr->getPwLock($ldap_user);
        $has_consent = $this->consent_mgr->hasActiveConsent($user);
        if($pwlock) {
            $pwlock->setTimezone('Europe/Prague');
        }
        return view('accountstatus', ['user' => $ldap_user->getFirstAttribute('uid'), 'locked' => $lock, 'pwlock' => $pwlock, 'consent' => $has_consent]);
    }
}
