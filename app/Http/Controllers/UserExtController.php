<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Auth\OidcUser;
use App\Models\Ldap\User as LdapUser;
use App\Models\User;
use App\Interfaces\UserExtManager;
use App\Services\OidcConnector;
use Illuminate\Validation\Rule;

class UserExtController extends Controller
{
    const LOCAL_USER_KEY = 'ext_user_local';
    const REMOTE_USER_KEY = 'ext_user_remote';
    const REMOTE_PROVIDER_KEY = 'ext_user_provider';

    const ALLOWED_PROVIDERS = ['NIA'];
    
    protected $ue_mgr;
    
    public function __construct(UserExtManager $mgr) {
        $this->ue_mgr = $mgr;
        $this->middleware(['model']);
        $this->middleware(['auth'])->only(['addIdentity', 'removeIdentity', 'confirmIdentity']);
    }
    
    public function showOverview(Request $request) {
        $user = Auth::user();
        if(empty($user)) {
            redirect()->setIntendedUrl(route('ext.home'));
            return view('ext.overview');
        }
        if($user instanceof OidcUser) {
            $ldap_user = $this->ue_mgr->findUserByExtIdentity($user->getAuthIdentifier());
            $auth_user = $user;
        } else {
            $this->rememberLocalUser($user);
            $ldap_user = $user->getLdapUser();
            $auth_user = $user->getAuthUser();
        }
        if(empty($ldap_user)) {
            $this->rememberRemoteUser($user);
            return view('ext.unlinked', ['auth_user' => $user, 'attrs' => $user->getAttributes() ]);
            $ext_ids = [];
        } else {
            if($user instanceof OidcUser) {
                $user = new User($auth_user, $ldap_user);
                Auth::guard()->login($user);
            }
            $ext_ids = $this->ue_mgr->listExternalIdentities($ldap_user);
        }
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user), "</pre>";
        #echo "<pre>", print_r(session()->all()), "</pre>";
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
        $provider = session(self::REMOTE_PROVIDER_KEY);
        $user_id = $auth_user->getAuthIdentifier();
        $ldap_user = LdapUser::findBy('cunipersonalid',  $user_id);
        if(!is_null($ldap_user)) {
            $user = new User($auth_user, $ldap_user);
            
            Auth::guard()->login($user);
        }
        if(empty($provider)) {
	    $provider = 'NIA';
        }
        $local_user = $this->rememberLocalUser($user);
        return redirect()->route('ext.confirm', ['provider' => $provider]);
        //$remote_user = session(self::REMOTE_USER_KEY);
        //return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user , 'provider' => $provider ]);
    }
    
    public function loginRemote(Request $request, $provider) {
        if(!Auth::attempt(['delegate' => $provider])) {
            return redirect()->route('ext.home')->withErrors(['failure' => __('External login failed.')]);
        }
        $remote_user = $this->rememberRemoteUser(Auth::user(), $provider);
	if(empty($remote_user)) {
	    return redirect()->route('ext.home')->withErrors(['failure' => __('No external identity found.')]);
	}
        return redirect()->route('ext.confirm', ['provider' => $provider]);
        //$local_user = session(self::LOCAL_USER_KEY);
        //return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user, 'provider' => $provider ]);
    }
    
    public function confirmIdentity(Request $request, $provider) {
        $remote_user = session(self::REMOTE_USER_KEY);
        $local_user = session(self::LOCAL_USER_KEY);
        return view('ext.confirm', [ 'local' => $local_user, 'remote' => $remote_user, 'provider' => $provider ]);
    }
    
    public function addIdentity(Request $request, $provider) {
        $local_user = session(self::LOCAL_USER_KEY);
        $remote_user = session(self::REMOTE_USER_KEY);
        $request->validate([
           'local' => [ 'required', Rule::in($local_user) ],
           'remote' => [ 'required', Rule::in($remote_user) ]
        ]);
        if(!in_array(Auth::user()->getAuthIdentifier(), [$local_user, $remote_user])) {
            return back()->withErrors(['failure' => __('Authenticated user has changed.')]);
        }
        if(!in_array($provider, self::ALLOWED_PROVIDERS)) {
            return back()->withErrors(['failure' => __('Unknown identity provider.')]);
        }
        $ldap_user = LdapUser::findBy('cunipersonalid', $local_user);
        if(empty($ldap_user)) {
            return back()->withErrors(['failure' => __('User not found.')]); 
        }
        $this->ue_mgr->setExtIdentity($ldap_user, $provider, $remote_user);
        if(Auth::user()->getAuthIdentifier() == $remote_user) {
            return redirect()->route('ext.ssoinfo')->with('status', __('External identity was successfully registered.'));
        }
        return redirect()->route('ext.home')->with('status', __('External identity was successfully registered.'));
    }
    
    public function removeIdentity(Request $request, $provider) {
        $auth_user = Auth::user();
        if($auth_user instanceof OidcUser) {
            $ldap_user = $this->ue_mgr->findUserByExtIdentity(Auth::user()->getAuthIdentifier());
        } else {
            $ldap_user = $auth_user->getLdapUser();
        }
        if(!in_array($provider, self::ALLOWED_PROVIDERS)) {
            return back()->withErrors(['failure' => __('Unknown identity provider.')]);
        }
        if(empty($ldap_user)) {
            return back()->withErrors(['failure' => __('User not found.')]);
        }
        $this->ue_mgr->removeExtIdentity($ldap_user, $provider);
        if($auth_user instanceof OidcUser) {
            Auth::logout();
        }
        return redirect()->route('ext.home')->with('status', __('External identity removed.'));
    }
    
    public function ssoInfo(Request $request) {
        redirect()->setIntendedUrl(route('ext.home'));
        return view('ext.ssoinfo');
    }
    
    protected function rememberRemoteUser(OidcUser $user, $provider = null) {
        $attrs = $user->getAttributes();
        if(array_key_exists('nia_identifier', $attrs)) {
            $id = $attrs['nia_identifier'];
            $provider = "NIA";
        }
	if(empty($id)) {
		return null;
	}
        session()->put(self::REMOTE_USER_KEY, $id);
        if(!empty($provider)) {
            session()->put(self::REMOTE_PROVIDER_KEY, $provider);
        }
        return $id;
    }
    
    protected function rememberLocalUser(User $user) {
        $id = $user->getAuthIdentifier();
        session()->put(self::LOCAL_USER_KEY, $id);
        return $id;
    }
}
