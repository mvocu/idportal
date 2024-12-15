<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Auth\OidcUser;
use App\Models\Ldap\User as LdapUser;
use App\Models\User;
use App\Interfaces\AuthenticationManager;
use Illuminate\Validation\Rule;
use App\Interfaces\IdentityResource;

class UserExtController extends Controller
{
    const LOCAL_USER_KEY = 'ext_user_local';
    const REMOTE_USER_KEY = 'ext_user_remote';
    const REMOTE_PROVIDER_KEY = 'ext_user_provider';
    const REMOTE_CLIENT_KEY = 'ext_user_client';

    const ALLOWED_PROVIDERS = ['NIA', 'eduid', 'edugain', 'svipeid'];
    
    protected $auth_mgr;
    
    public function __construct(AuthenticationManager $mgr) {
        $this->auth_mgr = $mgr;
        $this->middleware(['model']);
        $this->middleware(['auth'])->only(['addIdentity', 'removeIdentity', 'confirmIdentity']);
    }
    
    public function showOverview(Request $request) {
        $user = Auth::user();
        #Log::debug("Session1: ", ['session' => session()->all() ]);
        if(empty($user)) {
            $request->session()->flush();
            redirect()->setIntendedUrl(route('ext.home'));
            return view('ext.overview');
        }
        if($user instanceof OidcUser) {
            $ldap_user = $this->auth_mgr->findUserByExtIdentity($user->getAuthIdentifier());
            $auth_user = $user;
        } else {
            $this->rememberLocalUser($user);
            $ldap_user = $user->getLdapUser();
            $auth_user = $user->getAuthUser();
        }
        #Log::debug("Session2: ", ['session' => session()->all() ]);
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user), "</pre>";
        #echo "<pre>", print_r($this->ue_mgr->getIdentity($user)), "</pre>";
        #echo "<pre>", print_r(session()->all()), "</pre>";
        
        if(empty($ldap_user)) {
            $this->rememberRemoteUser($user);
            Log::info("Logged in user: ", ['user' => $user->getDisplayName(),  'auth' => $auth_user->getAuthIdentifier() ]);
            if($this->auth_mgr->isEligibleForRegistration($user)) {
                return view('ext.unlinked', [
                    'auth_user' => $user, 
                    'attrs' => $this->auth_mgr->getIdentity($user),
                    'provider' => $this->auth_mgr->getRemoteProvider($user),
                    'names' => IdentityResource::IDENTITY_ATTR_KEYS
                ]);
            } else {
                return view('ext.unusable', [
                    'auth_user' => $user,
                    'attrs' => $this->auth_mgr->getIdentity($user),
                    'provider' => $this->auth_mgr->getRemoteProvider($user),
                    'names' => IdentityResource::IDENTITY_ATTR_KEYS
                ]);
            }
        } else {
            if($user instanceof OidcUser) {
                $user = new User($auth_user, $ldap_user);
                Auth::guard()->login($user);
            }
            $ext_ids = $this->auth_mgr->listExternalIdentities($ldap_user);
        }

        #echo "<pre>", print_r($ext_ids), "</pre>";
        
        Log::info("Logged in user: ", ['user' => $user->getId(), 'ldap' => $ldap_user->getDn(), 'auth' => $auth_user->getAuthIdentifier() ]);

        return view('ext.overview', [
            'user' => $user, 
            'ldap_user' => $ldap_user, 
            'auth_user' => $auth_user,
            'ext_ids' => $ext_ids,
            'providers' => self::ALLOWED_PROVIDERS,
        ]);
    }

    public function loginLocal(Request $request) {
        if(!Auth::attempt(['delegate' => 'cas'])) {
            return redirect()->route('ext.home')->withErrors(['failure' => __('Login failed.')]);
        }
        $auth_user = Auth::user();
        $client = session(self::REMOTE_CLIENT_KEY);
        $user_id = $auth_user->getAuthIdentifier();
        $ldap_user = LdapUser::findBy('cunipersonalid',  $user_id);
        if(!is_null($ldap_user)) {
            $user = new User($auth_user, $ldap_user);
            
            Auth::guard()->login($user);
        }
        if(empty($client)) {
	       $client = '';
        }
        $local_user = $this->rememberLocalUser($user);
        #Log::debug("Session: ", ['session' => session()->all() ]);
        return redirect()->route('ext.confirm', ['client' => $client]);
        //$remote_user = session(self::REMOTE_USER_KEY);
        //return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user , 'provider' => $provider ]);
    }
    
    public function loginRemote(Request $request, $client) {
        if(Auth::hasUser() && !$request->hasAny(['code', 'cont'])) {
            # redirects and exits
            Auth::logout(route('ext.login.ext', ['client' => $client, 'cont' => 'true'], true));
        }
        if(!Auth::attempt(['delegate' => $client])) {
            return redirect()->route('ext.home')->withErrors(['failure' => __('External login failed.')]);
        }
        $remote_user = $this->rememberRemoteUser(Auth::user(), $client);
        if(empty($remote_user)) {
            return redirect()->route('ext.home')->withErrors(['failure' => __('No external identity found.')]);
	    }
	    if(!$this->auth_mgr->isEligibleForRegistration(Auth::user())) {
	        return redirect()->route('ext.home')->withErrors(['failure' => __('External identity not eligible.')]);
	    }
        #Log::debug("Session: ", ['session' => session()->all() ]);
        return redirect()->route('ext.confirm', ['client' => $client]);
        //$local_user = session(self::LOCAL_USER_KEY);
        //return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user, 'provider' => $provider ]);
    }
    
    public function confirmIdentity(Request $request, $client) {
        $remote_user = session(self::REMOTE_USER_KEY);
        $local_user = session(self::LOCAL_USER_KEY);
	    Log::debug("Local and remote user: ", ['local' => $local_user, 'remote' => $remote_user]);
        #Log::debug("Session: ", ['session' => session()->all() ]);
        return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user, 'client' => $client ]);
    }
    
    public function addIdentity(Request $request, $client) {
        $local_user = session(self::LOCAL_USER_KEY);
        $remote_user = session(self::REMOTE_USER_KEY);
        $provider = session(self::REMOTE_PROVIDER_KEY);
        $request->validate([
           'local' => [ 'required', Rule::in($local_user) ],
           'remote' => [ 'required', Rule::in($remote_user) ]
        ]);
        if(!in_array(Auth::user()->getAuthIdentifier(), [$local_user, $remote_user])) {
            return back()->withErrors(['failure' => __('Authenticated user has changed.')]);
        }
        if(!in_array($client, self::ALLOWED_PROVIDERS)) {
            return back()->withErrors(['failure' => __('Unknown identity provider.')]);
        }
        $ldap_user = LdapUser::findBy('cunipersonalid', $local_user);
        if(empty($ldap_user)) {
            return back()->withErrors(['failure' => __('User not found.')]); 
        }
        $this->auth_mgr->setExtIdentity($ldap_user, $client, $provider, $remote_user);
        if(Auth::user()->getAuthIdentifier() == $remote_user) {
            return redirect()->route('ext.ssoinfo')->with('status', __('External identity was successfully registered.'));
        }
        return redirect()->route('ext.home')->with('status', __('External identity was successfully registered.'));
    }
    
    public function removeIdentity(Request $request, $client) {
        $auth_user = Auth::user();
        if($auth_user instanceof OidcUser)  {
            $ldap_user = $this->auth_mgr->findUserByExtIdentity(Auth::user()->getAuthIdentifier());
        } else {
            $ldap_user = $auth_user->getLdapUser();
        }
        if(!in_array($client, self::ALLOWED_PROVIDERS)) {
            return back()->withErrors(['failure' => __('Unknown identity provider.')]);
        }
        if(empty($ldap_user)) {
            return back()->withErrors(['failure' => __('User not found.')]);
        }
        $request->validate([
            'provider' => [ 'sometimes', 'regex:/^[a-zA-Z.]+$/' ]
        ]);
        $provider = $request->input("provider", null);
        $this->auth_mgr->removeExtIdentity($ldap_user, $client, $provider);
        if($auth_user instanceof OidcUser) {
            Auth::logout();
        }
        return redirect()->route('ext.home')->with('status', __('External identity removed.'));
    }
    
    public function ssoInfo(Request $request) {
        // this may not be necessary anymore
        redirect()->setIntendedUrl(route('ext.home'));
        return view('ext.ssoinfo');
    }
    
    protected function rememberRemoteUser(OidcUser $user, $client = null) {
	    $id = $user->getAuthIdentifier();
	    $provider = $this->auth_mgr->getRemoteProvider($user);
        if(empty($id)) {
            return null;
        }
        session()->put(self::REMOTE_USER_KEY, $id);
        if(!empty($provider)) {
            session()->put(self::REMOTE_PROVIDER_KEY, $provider);
        } else {
            session()->forget(self::REMOTE_PROVIDER_KEY);
        }
        if(!empty($client)) {
            session()->put(self::REMOTE_CLIENT_KEY, $client);
        } else {
            session()->forget(self::REMOTE_CLIENT_KEY);
        }
        return $id;
    }
    
    protected function rememberLocalUser(User $user) {
        $id = $user->getAuthIdentifier();
        session()->put(self::LOCAL_USER_KEY, $id);
        return $id;
    }
}
