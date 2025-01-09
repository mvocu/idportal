<?php
namespace App\Services;

#use App\Auth\AnonymousUser;
use App\Interfaces\ResetManager as ResetManagerInterface;
use App\Models\Ldap\User as LdapUser;
#use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\AuthenticationManager;
use App\Interfaces\IdentityManager;

class ResetManager implements ResetManagerInterface
{
    const RESET_METHODS = [
        'NIA' => [],
        'svipeid' => [],
        'eduid' => [],
        'edugain' => [],
        'sms-challenge' => [],
        'mail-challenge' => [],
    ];
    
    const TARGET_USER_KEY = "reset_target";
    const REMOTE_IDENTITY_KEY = "reset_ext_id";
    const METHOD_KEY = "reset_method";
    const VERIFICATION_KEY = "reset_verification";

    protected $_request;
    protected $auth_mgr;
    
    public function __construct(Request $request, AuthenticationManager $auth_mgr) {
        $this->_request = $request;
        $this->auth_mgr = $auth_mgr;
    }
    
    public function getAvailableMethods(LdapUser $model = null) 
    {
        if($model == null) {
            $model = $this->retrieveTargetUser();
        }
        if($model == null) {
            return collect(array_keys(self::RESET_METHODS));
        }
        #$user = new User(new AnonymousUser(), $model);
        $gate = app(Gate::class)->forUser($model);
        // class of object passed to allows() method determines the policy class
        $methods = collect(array_keys(self::RESET_METHODS))
            ->filter(fn($it) => $gate->allows("password-reset-using-" . $it, $this));
        return $methods;;
    }
    
    public function getMethodDescription($method = null)
    {
        if($method == null || !array_key_exists($method, self::RESET_METHODS)) {
            return self::RESET_METHODS;
        }
        return self::RESET_METHODS[$method];
    }
    
    public function determineRequiredScore($user) 
    {
        if(!$this->auth_mgr->hasAuthentication($user)) {
            return IdentityManager::COMPARISON_PURPOSE_INITIAL;
        }
        $gate = app(Gate::class)->forUser($user);
        if($gate->allows("password-reset-weak", $this)) {
            return IdentityManager::COMPARISON_PURPOSE_MERGE;
        }
        return IdentityManager::COMPARISON_PURPOSE_MERGE_TRUSTED;
    }
    
    public function rememberTargetUser(LdapUser $user) {
        $this->_request->session()->put(self::TARGET_USER_KEY, $user->getDn());
    }
    
    public function retrieveTargetUser() {
        $dn = $this->_request->session()->get(self::TARGET_USER_KEY);
        if(empty($dn)) {
            return null;
        }
        return LdapUser::find($dn);
    }
    
    public function forgetTargetUser() {
        $this->_request->session()->forget(self::TARGET_USER_KEY);
        $this->forgetVerificationResult();
    }
    
    public function rememberRemoteIdentity($identity) {
        $this->_request->session()->put(self::REMOTE_IDENTITY_KEY, $identity);
    }
    
    public function retrieveRemoteIdentity() {
        $identity = $this->_request->session()->get(self::REMOTE_IDENTITY_KEY);
        if(empty($identity) && Auth::check()) {
            $identity = $this->auth_mgr->getIdentity(Auth::user());
            $this->rememberRemoteIdentity($identity);
        }
        return $identity;
    }
    
    public function forgetRemoteIdentity() {
        $this->_request->session()->forget(self::REMOTE_IDENTITY_KEY);
        $this->forgetVerificationResult();
    }
    
    public function saveMethod($method) {
        $this->_request->session()->put(self::METHOD_KEY, $method);
    }
    
    public function retrieveMethod() {
        return $this->_request->session()->get(self::METHOD_KEY);
    }
    
    public function forgetMethod() {
        $this->_request->session()->forget(self::METHOD_KEY);
        $this->forgetVerificationResult();
    }
    
    public function saveVerificationResult($result) {
        $this->_request->session()->put(self::VERIFICATION_KEY, $result);
    }
    
    public function retrieveVerificationResult() {
        return $this->_request->session()->get(self::VERIFICATION_KEY);
    }
    
    public function forgetVerificationResult() {
        $this->_request->session()->forget(self::VERIFICATION_KEY);
    }
    
}

