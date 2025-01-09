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
use App\Interfaces\IdentityResource;
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
        $this->middleware(['model'])->only(['showSearch', 'verifyUser']);
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
        #echo "<pre>", print_r(session(), true), "</pre>";
        switch($results->count()) {
            case 0:
                return redirect()->back()->withErrors(['failure' => __('No user account found.')]);
                
            case 1:
                $user = $results->first();
                $this->reset_mgr->rememberTargetUser($user);
                break;
                
            default:
                return redirect()->back()->withErrors(['failure' => __('There is more than one account corresponding to given data.') ]);
        }
        // if possible, fill-in missing data in remote identity
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if($this->_mergeInputToIdentity($identity, Arr::only($data, ['given_name', 'family_name', 'birthdate']))) {
            $this->reset_mgr->rememberRemoteIdentity($identity);
        }
        return redirect()->route('reset.idcheck');
    }
    
    public function showMethods(Request $request) {
        $methods = $this->reset_mgr->getAvailableMethods();
        return view('reset.methods', ['methods' => $methods, 'user' => Auth::user()]);
    }
  
    public function verifyUser(Request $request, $method) {
        $model = $this->reset_mgr->retrieveTargetUser();
        #if(!$model) {
        #    return redirect()->route('reset.home')->withErrors(['failure' => __('Target user account not specified.')]);
        #}
        switch($method) {
            case 'NIA':
            case 'svipeid':
            case 'eduid':
            case 'edugain':
                $this->reset_mgr->saveMethod($method);
                return $this->_verifyUserByRemoteClient($request, $method, $model);
                
            case 'sms-challenge':
                break;
                
            case 'mail-challenge':
                $this->reset_mgr->saveMethod($method);
                return redirect()->route('reset.search');
        }
        return back()->withErrors(['failure' => __('Selected method is not supported.')]);
    }
    
    /*
     * posts to checkIdentity
     */
    public function showInquiry(Request $request) {
        $user = Auth::user();
        $model = $this->reset_mgr->retrieveTargetUser();
        $user_r = empty($model) ? [] : $this->u_mgr->getIdentity($model);
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user, true), "</pre>";
        #echo "<pre>", print_r($identity, true), "</pre>";
        return view('reset.inquiry', ['user' => $user, 'identity' => $identity,
            'target' => $user_r,
            'score' => $this->id_mgr->getLastScore()]);
    }
    
    public function mergeData(Request $request) {
        $form = new FormResource($request->all());
        $data = $form->toArray($request);
        #$data = $request->only(['given_name', 'family_name', 'birthdate', 'phone_number', 'email']);
        #$address = $request->only(['street', 'street_number', 'evidence_number', 'city', 'postal_code', 'country']);
        #echo "<pre>", print_r($data, true), "</pre>";
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
        // verify email_ token with phone_number
        if(!empty($data['email'])) {
            if(!$this->ch_mgr->verifyToken(ChallengeManager::EMAIL_CHALLENGE_KEY, $this->ch_store,
                $request->input('email_challenge'))) {
                    return redirect()->back()->withInput()->withErrors([
                        'failure' => __('Unverified email address.'),
                        'email' => [ __('Email address must be verified.') ]
                    ]);
                }
                $data['email_verified'] = 1;
        }
        #if(!empty(array_filter($address, function ($val) { return !empty($val); }))) {
        if(!empty($data['address'])) {
            $validator = Validator::make($data['address'], self::ADDRESS_VALIDATION_RULES);
            if(!$validator->passes()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }
            #$data['address'] = $address;
        }
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if($this->_mergeInputToIdentity($identity, $data)) {
            $this->reset_mgr->rememberRemoteIdentity($identity);
        }
        #echo "<pre>", var_dump($identity), "</pre>"; exit;
        return redirect()->route('reset.idcheck');
    }
    
    /*
     * Final (and possibly repeated) stop before heading to password.
     */
    public function checkIdentity(Request $request) {
        $model = $this->reset_mgr->retrieveTargetUser();
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if(empty($identity)) {
            $method = $this->reset_mgr->retrieveMethod();
            if($method == 'mail-challenge') {
                return redirect()->route('reset.inquiry')->withInput(
                    $request->only(['given_name', 'family_name', 'birthdate', 'administrative_number']));
            }
            return redirect()->route('reset.methods')->withErrors(['failure' => __('No identity established yet.')]);
        }
        if(empty($identity[IdentityResource::LOA])) {
            return redirect()->route('reset.inquiry')->withInput()
                ->withErrors(['failure' => 
                    __('The identity presented does not contain enough information for identification. Please review and provide additional data.')]);            
        }
        if(empty($model)) {
            return redirect()->route('reset.find')
                ->withInput(Arr::only($identity, ['given_name', 'family_name', 'birthdate' ]))
                ->with(['warning' => __('You have to specify target account first.') ]);
        }
        $user_r = $this->u_mgr->getIdentity($model);
        $purpose = $this->reset_mgr->determineRequiredScore($model);
        $same = $this->id_mgr->compareIdentity($user_r, $identity, $purpose);
        #echo "<br><br><br><br>";
        #echo "<pre>", print_r($user_r, true), "</pre>";
        #echo "<pre>", print_r($model, true), "</pre>";
        #echo "<pre>", print_r($identity, true), "</pre>";
        #echo "<pre>", print_r($same, true), "</pre>";
        switch($same) {
            case IdentityManager::IDENTITY_RESULT_SAME:
                $this->reset_mgr->saveVerificationResult(true);
                return redirect()->route('reset.password')->with(['status' => __('Your identity has been verified.')]);
                
            case IdentityManager::IDENTITY_RESULT_DIFFERENT:
                $this->reset_mgr->saveVerificationResult(false);
                return redirect()->route('reset.failed')
                    ->withErrors(['failure' => __('The identity presented does not match target account.', 
                        [ 
                            'score' => $this->id_mgr->getLastScore(),
                            'required' => $this->id_mgr->getRequiredScore($purpose),
                            'error' => $this->id_mgr->getLastError(),
                        ])]);
                
            case IdentityManager::IDENTITY_RESULT_UNKNOWN:
                break;
        }
        if($this->id_mgr->hasAllInformation($identity)) {
            return redirect()->route('reset.failed')->withErrors(['failure' => __('Identity check could not be completed.')]);
        }
        return redirect()->route('reset.inquiry')
            #->withInput($identity)
            ->with(['warning' => __('More information is required to verify your identity.',
                [
                    'score' => $this->id_mgr->getLastScore(),
                    'required' => $this->id_mgr->getRequiredScore($purpose),
                    'error' => $this->id_mgr->getLastError()
                ]
            )]);
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
        $model = $this->reset_mgr->retrieveTargetUser();
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        $this->reset_mgr->forgetRemoteIdentity();
        $this->reset_mgr->forgetTargetUser();
        $request->session()->put('url.intended', route('reset.home'));
        return view('reset.failed', ['model' => $model, 'identity' => $identity ]);
    }
    
    public function cleanRemote(Request $request) {
        $this->reset_mgr->forgetRemoteIdentity();
        return redirect()->route('reset.inquiry')->with(['status' => __('Collected data have been cleaned.')]);
    }

    public function showPasswordForm(Request $request) {
        # TODO: check identity and verification result
        $verified = $this->reset_mgr->retrieveVerificationResult();
        $model = $this->reset_mgr->retrieveTargetUser();
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if(!$verified || empty($model) || empty($identity)) {
            return redirect()->back()->withErrors(['failure' => 'Your identity has not been verified.']);
        }
        # XXX: maybe make the above code part of the authorization check in ResetManagerPolicy
        #$this->authorizeForUser($model, 'password-reset', [ 'identity' => $identity ]);
        return view('reset.passwordform');
    }
    
    public function changePassword(Request $request) {
        $verified = $this->reset_mgr->retrieveVerificationResult();
        $model = $this->reset_mgr->retrieveTargetUser();
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if(!$verified || empty($model) || empty($identity)) {
            return redirect()->back()->withErrors(['failure' => 'Your identity has not been verified.']);
        }
        #$this->authorizeForUser($model, 'password-reset', [ 'identity' => $identity ]);
        
    }
    
    protected function _verifyUserByRemoteClient(Request $request, $client, $model) {
        # see UserExtController::loginRemote()
        $identity = $this->reset_mgr->retrieveRemoteIdentity();
        if((!empty($identity) || Auth::check()) 
            && !$request->hasAny(['code', 'cont'])) {
            // we already have an identity which we need to replace with a new login
            $this->reset_mgr->forgetRemoteIdentity();
            Auth::logout(route('reset.verify', ['method' => $client, 'cont' => 'true'], true));
        } else {
            if(!Auth::attempt(['delegate' => $client])) {
                return redirect()->route('reset.methods')->withErrors(['failure' => __('External login failed.')]);
            }
        }
        $user = Auth::user();
        // if we have got user with ldap model, it means the external identity is registered 
        // and we may proceed directly to password
        if($user instanceof User && $user->hasLdapUser()) {
            return redirect()->route('password.home');
        }
        $identity = $this->auth_mgr->getIdentity($user);
        $this->reset_mgr->rememberRemoteIdentity($identity);
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
                $this->reset_mgr->rememberTargetUser($user);
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
    
    # possibly move this to IdentityManager and ad more intelligence
    protected function _mergeInputToIdentity(&$identity, $data) {
        $changed = false;
        
        # XXX: use attr names from IdentityResource, as we are dealing with resources here
        foreach(['given_name', 'family_name', 'birthdate'] as $key) {
            if(!isset($identity[$key]) && isset($data[$key])) {
                $changed = true;
                $identity[$key] = $data[$key];
            }
        }
        if($changed) {
            if(!empty($identity['email'] || !empty($identity['phone_number']))) {
                if(empty($identity['loa'])) {
                    $identity['loa'] = IdentityManager::LOA_NONE;
                }
            }
        }
        #if($changed) {
        #    // changing personal data resets LoA
        #    if(empty($data['loa'])) {
        #        unset($identity['loa']);
        #    } else {
        #        $identity['loa'] = $data['loa'];
        #    }
        #}
        $added = false;
        foreach(['email', 'phone_number'] as $key) {
            if(!empty($data[$key])) {
                if(!isset($identity[$key])) {
                    $identity[$key] = [] ;
                } else if(!is_array($identity[$key])) {
                    $identity[$key] = [ $identity[$key] ];
                }
                if(!in_array($data[$key], $identity[$key])) {
                    $changed = true;
                    $added = true;
                    $identity[$key][] = $data[$key];
                }
            }
        }
        if($added && empty($identity['loa'])) {
            // adding verified contacts may set some LoA
            $identity['loa'] = $data['loa'];
        }
        if(isset($data['address'])) {
            foreach($data['address'] as $key => $value) {
                if(!empty($value)) {
                    $changed = true;
                    $identity['address'][$key] = $value;
                }
            }
        }
        if(!isset($identity['administrative_number']) && isset($data['administrative_number'])) {
            $changed = true;
            $identity['administrative_number'] = $data['administrative_number'];
            if(!empty($identity['loa']) && $identity['loa'] != IdentityManager::LOA_NONE) {
                if(empty($identity['email'] && empty($identity['phone_number']))) {
                    $identity['loa'] = null;
                } else {
                    $identity['loa'] = IdentityManager::LOA_NONE;
                }
            }
        }
        foreach(['cuni_personalid', 'cuni_card_id'] as $key) {
            if(!isset($identity[$key]) && isset($data[$key])) {
                $changed = true;
                $identity[$key] = $data[$key];
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
