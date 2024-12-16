<?php

namespace App\Http\Controllers;

use App\Interfaces\AuthenticationManager;
use App\Models\Ldap\User as LdapUser;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\ResetManager;
use App\Models\User;
use App\Interfaces\IdentityManager;
use App\Interfaces\UserManager;
use App\Interfaces\ChallengeManager;
use App\Interfaces\ChallengeStore;
use App\Http\Resources\FormResource;

class ResetController extends Controller
{
    const FIND_VALIDATION_RULES = [
        'cunipersonalid' => 'required_without:given_name,family_name,birthdate|string',
        'given_name' => 'required_without_all:identifier,cunipersonalid|string',
        'family_name' => 'required_without_all:identifier,cunipersonalid|string',
        'birthdate' => 'required_without_all:identifier,cunipersonalid|date',
    ];
    
    const ADDRESS_VALIDATION_RULES = [
        'street' => 'required|string',
        'street_number' => 'required_without:evidence_number|integer',
        'evidence_number' => 'required_without:street_number|integer',
        'city' => 'required|string',
        'postal_code' => 'required|integer',
        'country' => 'required|string',
    ];
    
    const PERSON_VALIDATION_RULES = [
        'given_name' => 'sometimes|nullable|string',
        'family_name' => 'sometimes|nullable|string', 
        'birthdate' => 'sometimes|nullable|date',
        'email' => 'sometimes|nullable|email',
        'phone_number' => 'sometimes|nullable|phone',
    ];
    
    const TARGET_USER_KEY = "reset_target";
    const REMOTE_IDENTITY_KEY = "reset_ext_id";
    const METHOD_KEY = "reset_method";
    
    protected $u_mgr;
    protected $auth_mgr;
    protected $reset_mgr;
    protected $id_mgr;
    protected $ch_mgr;
    protected $ch_store;
    
    public function __construct(
            UserManager $u_mgr, 
            AuthenticationManager $auth_mgr, 
            ResetManager $reset_mgr, 
            IdentityManager $id_mgr,
            ChallengeManager $ch_mgr,
            ChallengeStore $ch_store
        ) {
        $this->u_mgr = $u_mgr;
        $this->auth_mgr = $auth_mgr;
        $this->reset_mgr = $reset_mgr;
        $this->id_mgr = $id_mgr;
        $this->ch_mgr = $ch_mgr;
        $this->ch_store = $ch_store;
        $this->middleware(['model'])->only(['showSearch']);
    }
    
    public function showSearch(Request $request) {
        if(Auth::check() && Auth::user() instanceof User && Auth::user()->hasLdapUser()) {
            return redirect()->route('password.home');
        }
        return view('reset.search');
    }
        
    public function findUser(Request $request) {
        $data = $this->validate($request, self::FIND_VALIDATION_RULES);
        $results = $this->_findUser($data);
        #echo "<br><br><br><br>";
        #echo "<pre>", $query->getUnescapedQuery(), "</pre>"; exit;
        switch($results->count()) {
            case 0:
                return redirect()->back()->withErrors(['failure' => __('No user account found.')]);
                
            case 1:
                $user = $results->first();
                $this->_rememberTargetUser($request, $user);
                break;
                
            default:
                return redirect()->back()->withErrors(['failure' => __('There is more than one account corresponding to given data.') ]);
        }
        // if possible, fill-in missing data in remote identity
        $identity = $this->_retrieveRemoteIdentity($request);
        if($this->_mergeInputToIdentity($identity, Arr::only($data, ['given_name', 'family_name', 'birthdate']))) {
            $this->_rememberRemoteIdentity($request, $identity);
        }
        return redirect()->route('reset.idcheck');
    }
    
    public function showMethods(Request $request) {
        $model = $this->_retrieveTargetUser($request);
        #if(!$model) {
        #    return redirect()->route('reset.home')->withErrors(['failure' => __('Target user account not specified.')]);
        #}
        $methods = $this->reset_mgr->getAvailableMethods($model);
        return view('reset.methods', ['methods' => $methods, 'user' => Auth::user()]);
    }
  
    public function verifyUser(Request $request, $method) {
        $model = $this->_retrieveTargetUser($request);
        #if(!$model) {
        #    return redirect()->route('reset.home')->withErrors(['failure' => __('Target user account not specified.')]);
        #}
        switch($method) {
            case 'NIA':
            case 'svipeid':
            case 'eduid':
            case 'edugain':
                return $this->_verifyUserByRemoteClient($request, $method, $model);
                
            case 'sms-challenge':
                break;
                
            case 'mail-challenge':
                break;
        }
        return back()->withErrors(['failure' => __('Selected method is not supported.')]);
    }
    
    /*
     * posts to checkIdentity
     */
    public function showInquiry(Request $request) {
        $user = Auth::user();
        $identity = $this->_retrieveRemoteIdentity($request);
        if(empty($identity) && Auth::check()) {
            $identity = $this->auth_mgr->getIdentity($user);
        }
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user, true), "</pre>";
        #echo "<pre>", print_r($identity, true), "</pre>";
        return view('reset.inquiry', ['user' => $user, 'identity' => $identity, 'score' => $this->id_mgr->getLastScore()]);
    }
    
    public function mergeData(Request $request) {
        $form = new FormResource($request->all());
        $data = $form->toArray($request);
        #$data = $request->only(['given_name', 'family_name', 'birthdate', 'phone_number', 'email']);
        #$address = $request->only(['street', 'street_number', 'evidence_number', 'city', 'postal_code', 'country']);
        $validator = Validator::make($data, self::PERSON_VALIDATION_RULES);
        if(!$validator->passes()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }
        // verify phone_number token with phone_number
        if(!empty($data['phone_number'])) {
            if(!$this->ch_mgr->verifyToken(ChallengeManager::PHONE_CHALLENGE_KEY, $this->ch_store, 
                $request->input('phone_challenge'))) {
                    return redirect()->back()->withInput()->withErrors([
                        'failure' => __('Unverified phone number.'),
                        'phone_number' => [ __('Phone number must be verified.') ]
                    ]);
            }
            $data['phone_number_verified'] = 1;
        }
        #if(!empty(array_filter($address, function ($val) { return !empty($val); }))) {
        if(!empty($data['address'])) {
            $validator = Validator::make($data['address'], self::ADDRESS_VALIDATION_RULES);
            if(!$validator->passes()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }
            #$data['address'] = $address;
        }
        $identity = $this->_retrieveRemoteIdentity($request);
        if($this->_mergeInputToIdentity($identity, $data)) {
            $this->_rememberRemoteIdentity($request, $identity);
        }
        #echo "<pre>", var_dump($identity), "</pre>";
        return redirect()->route('reset.idcheck');
    }
    
    /*
     * Final (and possibly repeated) stop before heading to password.
     */
    public function checkIdentity(Request $request) {
        $model = $this->_retrieveTargetUser($request);
        $identity = $this->_retrieveRemoteIdentity($request);
        if(empty($identity) && Auth::check()) {
            $identity = $this->auth_mgr->getIdentity(Auth::user());
        }
        if(empty($identity)) {
            return redirect()->route('reset.methods')->withErrors(['failure' => __('No identity established yet.')]);
        }
        if(empty($model)) {
            return redirect()->route('reset.find')
                ->withInput(Arr::only($identity, ['given_name', 'family_name', 'birthdate' ]))
                ->with(['warning' => __('You have to specify target account first.') ]);
        }
        $user_r = $this->u_mgr->getIdentity($model);
        if($this->auth_mgr->hasAuthentication($model)) {
            $same = $this->id_mgr->compareIdentity($user_r, $identity, IdentityManager::COMPARISON_PURPOSE_MERGE);
            
        } else {
            $same = $this->id_mgr->compareIdentity($user_r, $identity, IdentityManager::COMPARISON_PURPOSE_INITIAL);
        }
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user_r, true), "</pre>";
        #echo "<pre>", print_r($model, true), "</pre>";
        #echo "<pre>", print_r($identity, true), "</pre>";
        #echo "<pre>", print_r($same, true), "</pre>";
        switch($same) {
            case IdentityManager::IDENTITY_RESULT_SAME:
                return redirect()->route('password.home')->with(['status' => __('Your identity has been verified.')]);
                
            case IdentityManager::IDENTITY_RESULT_DIFFERENT:
                return redirect()->route('reset.failed')
                    ->withErrors(['failure' => __('The identity presented does not match target account.')]);
                
            case IdentityManager::IDENTITY_RESULT_UNKNOWN:
                break;
        }
        if($this->id_mgr->hasAllInformation($identity)) {
            return redirect()->route('reset.failed')->withErrors(['failure' => __('Identity check could not be completed.')]);
        }
        return redirect()->route('reset.inquiry')
            ->withInput($identity)
            ->with(['warning' => __('More information is required to verify your identity.')]);
    }

    public function checkFailed(Request $request) {
        #if(Auth::check() && !$request->has('cont')) {
        #    // logout and come back if authenticated
        #    Auth::logout(route('reset.failed', ['cont' => 'true'], true));
        #}
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r(Auth::user(), true), "</pre>";
        #echo "<pre>", print_r($model, true), "</pre>";
        #echo "<pre>", print_r(Auth::hasUser(), true), "</pre>";
        #echo "<pre>", print_r($request->has('cont'), true), "</pre>";
        $model = $this->_retrieveTargetUser($request);
        $identity = $this->_retrieveRemoteIdentity($request);
        if(empty($identity) && Auth::check()) {
            $identity = $this->auth_mgr->getIdentity(Auth::user());
        }
        $this->_forgetRemoteIdentity($request);
        $this->_forgetTargetUser($request);
        $request->session()->put('url.intended', route('reset.home'));
        return view('reset.failed', ['model' => $model, 'identity' => $identity ]);
    }
    
    public function cleanRemote(Request $request) {
        $this->_forgetRemoteIdentity($request);
        return redirect()->route('reset.inquiry')->with(['status' => __('Collected data have been cleaned.')]);
    }
    
    protected function _verifyUserByRemoteClient(Request $request, $client, $model) {
        # see UserExtController::loginRemote()
        if(($request->session()->has(self::REMOTE_IDENTITY_KEY) || Auth::check()) 
            && !$request->hasAny(['code', 'cont'])) {
            // we already have an identity which we need to replace with a new login
            $this->_forgetRemoteIdentity($request);
            Auth::logout(route('reset.verify', ['method' => $client, 'cont' => 'true'], true));
        } else {
            if(!Auth::attempt(['delegate' => $client])) {
                return redirect()->route('reset.methods')->withErrors(['failure' => __('External login failed.')]);
            }
        }
        $user = Auth::user();
        $identity = $this->auth_mgr->getIdentity($user);
        $this->_rememberRemoteIdentity($request, $identity);
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user, true), "</pre>";
        #echo "<pre>", print_r($identity, true), "</pre>"; 
        // try to find target user 
        $validator = Validator::make($identity, self::FIND_VALIDATION_RULES);
        // XXX - make birthdate not required (possible to fill in additionaly)
        if($validator->fails()) {
            // received data do not contain enough information to find target user
            
            #return redirect()->route('reset.failed')->withErrors([
            #    'failure' => __('Information received from external provider is not sufficient to establish your identity.')]);
            return redirect()->route('reset.find')
                ->withInput(Arr::only($identity, ['given_name', 'family_name', 'birthdate' ]))
                ->with(['warning' => __('More information is required to find your account.')]);
        }
        #echo "<pre>", print_r($validator->validated(), true), "</pre>"; exit;
        $results = $this->_findUser($validator->validated());
        switch($results->count()) {
            case 0:
                return redirect()->route('reset.find')
                    ->withInput(Arr::only($identity, ['given_name', 'family_name', 'birthdate' ]))
                    ->with([
                    'warning' => __('We could not find any account matching data obtained from your external identity.')
                ]);
                break;
                
            case 1:
                $user = $results->first();
                $this->_rememberTargetUser($request, $user);
                break;
                
            default:
                // filter the candidates by the more specific information in remote identity, if available
                break;
                
        }
        return redirect()->route('reset.idcheck');
    }
    
    protected function _findUser($data) {
        if(isset($data['cunipersonalid'])) {
            $results = $this->u_mgr->findUserByIdentifier($data['cunipersonalid']);
        } else {
            $results = $this->u_mgr->findUserByData($data);
        }
        return $results;
    }
    
    protected function _rememberTargetUser(Request $request, LdapUser $user) {
        $request->session()->put(self::TARGET_USER_KEY, $user->getDn());
    }
    
    protected function _retrieveTargetUser(Request $request) {
        $dn = $request->session()->get(self::TARGET_USER_KEY);
        if(empty($dn)) {
            return null;
        }
        return LdapUser::find($dn);
    }
    
    protected function _forgetTargetUser(Request $request) {
        $request->session()->forget(self::TARGET_USER_KEY);
    }
    
    protected function _rememberRemoteIdentity(Request $request, $identity) {
        $request->session()->put(self::REMOTE_IDENTITY_KEY, $identity);
    }
    
    protected function _retrieveRemoteIdentity(Request $request) {
        return $request->session()->get(self::REMOTE_IDENTITY_KEY);
    }
    
    protected function _forgetRemoteIdentity(Request $request) {
        $request->session()->forget(self::REMOTE_IDENTITY_KEY);
    }

    protected function _saveMethod(Request $request, $method) {
        $request->session()->put(self::METHOD_KEY, $method);    
    }
    
    protected function _retrieveMethod(Request $request) {
        return $request->session()->get(self::METHOD_KEY);
    }
    
    protected function _forgetMethod(Request $request) {
        $request->session()->forget(self::METHOD_KEY);
    }
    
    protected function _mergeInputToIdentity(&$identity, $data) {
        $changed = false;
        
        foreach(['given_name', 'family_name', 'birthdate'] as $key) {
            if(!isset($identity[$key]) && isset($data[$key])) {
                $changed = true;
                $identity[$key] = $data[$key];
            }
        }
        foreach(['email', 'phone_number'] as $key) {
            if(isset($data[$key])) {
                if(!isset($identity[$key])) {
                    $identity[$key] = [] ;
                } else if(!is_array($identity[$key])) {
                    $identity[$key] = [ $identity[$key] ];
                }
                $changed = true;
                $identity[$key][] = $data[$key];
            }
        }
        if(isset($data['address'])) {
            foreach($data['address'] as $key => $value) {
                if(!empty($value)) {
                    $changed = true;
                    $identity['address'][$key] = $value;
                }
            }
        }
        return $changed;
    }
    
    /*
     * Check whether the attributes from external IdP contain enough data to establish user identity.
     * The result depends on:
     *    - the data provided by external IdP
     *    - whether the target user contains external id from used external IdP
     *    - whether the target user already has means of authentication (password or any external identity) 
     */
    protected function _validateExternalIdentity($client, LdapUser $model, $identity) {
        $validator = Validator::make($identity, self::FIND_VALIDATION_RULES);
        return $validator->passes();
    }
}
