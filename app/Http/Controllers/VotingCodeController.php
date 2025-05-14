<?php
namespace App\Http\Controllers;

use App\User;
use App\Auth\PhoneOwner;
use App\Auth\VotingUser;
use App\Http\Resources\ExtUserResource;
use App\Interfaces\VotingCodeManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\ExtSourceManager;
use App\Interfaces\LdapConnector;
use App\Traits\FindsExternalAccount;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Models\Database\Contact;
use App\Models\Database\ExtSource;
use App\Interfaces\RegistrationManager;
use App\Traits\AuthorizesBySMS;

class VotingCodeController extends Controller
{
    use FindsExternalAccount;
    
    use AuthorizesBySMS;
    
    protected $voting_code_mgr;
    protected $ext_source_mgr;
    protected $user_ext_mgr;
    protected $user_mgr;
    protected $reg_mgr;
    
    # declared in FindsExternalAccount:
    #protected $ldap_mgr;
    
    public function __construct(
        VotingCodeManager $voting_code_mgr,
        ExtSourceManager $ext_source_mgr,
        UserExtManager $user_ext_mgr,
        UserManager $user_mgr,
        LdapConnector $ldap_mgr,
        RegistrationManager $reg_mgr
        )
    {
        $this->voting_code_mgr = $voting_code_mgr;
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->user_mgr = $user_mgr;
        $this->ldap_mgr = $ldap_mgr;
        $this->reg_mgr = $reg_mgr;
        
        $this
            ->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'finduser'])
            ->except(['showCode', 'getCode', 'declare', 'showRegistrationForm', 'registerExt']);
        $this
            ->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'finduser', 'auth'])
            ->only(['showCode', 'getCode', 'declare']);
        $this->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita'])
            ->only(['showRegistrationForm']);
        $this->middleware('eidp')->only('registerExt');
        
    }
    
    public function showRegistrationForm(Request $request) 
    {
        # if there is logged in registered user, go on to get or show the voting code
        if(Auth::user() instanceof \App\User) {
            $user = Auth::user();
            if($this->voting_code_mgr->hasActiveVotingCode($user->getDatabaseUser())) {
                return redirect()->route('voting.show');
            } else {
                return redirect()->route('voting.get');
            }
        }

        # check if we have a logged in user in some of the idps
        $idps = $this->ext_source_mgr->listAuthenticators()->pluck('name');
        $auth_user = null;
        $client = null;
        foreach ($idps as $idp) {
            # somewhat similar to Authenticate middleware, but does not throw exception when unauthenticated
            if(Auth::guard($idp)->check()) {
                $client = $idp;
                $auth_user = Auth::guard($idp)->user();
            }
        }
        # having logged in using external identity, we have to get to the actual user differently
        # 
        $user = null;
        $user_r = null;
        if($client != null) {
            #$auth_user = Auth::guard($client)->user();
            $users = $this->findUserByExtIdentity($auth_user, $client, /* OUT: UserResource array */ $user_r);
            if($users->count() == 1) {
                $user = $users->first();
                $ldap_user = $this->ldap_mgr->findUser($user);
                # convert to App\User (see middleware ExternalIdpAuthenticateSession)
                $user = new User([], $ldap_user->getQuery());
                $user->setRawAttributes($ldap_user->getAttributes());
            
            } else {
                $user = null;
            }
        }
        
        if( false && !empty($user))
        {
            if($this->voting_code_mgr->hasActiveVotingCode($user->getDatabaseUser())) {
                return redirect()->route('voting.show');
            } else {
                return redirect()->route('voting.get');
            }
        }

        return view('votingregister', 
            [ 'idp' => $idps, 'user' => $user, 'extuser' => $auth_user, 'user_r' => $user_r, 'client' => $client ]);
    }

    public function checkRegistration(Request $request)
    {
        $data = $request->all();
        if(!empty($data['phone'])) {
            $contact = new Contact();
            $contact->setAttribute('phone', $data['phone']);
            $data['phone'] = $contact->phone;
        }
        $this->validator($data)->validate();
        
        $resource = $this->reg_mgr->getExtUserResource($data);
        
        # TODO: check for existence of this user
        
        
        # we have all the data validated, create validation token and send it by preferred method
        if($request->input('preferred') == 'sms') {
            # send token by SMS
            # see trait AuthorizesBySMS::sendAuthorizationToken (but that is async, not usable here)
            $phone_user = new PhoneOwner($data['phone']);
            $phone_user->sendPasswordResetNotification($this->broker()->getRepository()->create($phone_user));
        } else {
            # send token by e-mail 
            # see trait SendAccountActivationEmail::sendActivationLink (but we have to use different user with different message) 
            $user = new VotingUser($data['email']);
            $this->activationMgr()->sendActivationLink($user);
        }
        
        $request->session()->put('voting.user', $resource);
        $request->session()->put('voting.verification', $request->input('preferred'));
        
        # go to validation code check
        return redirect()->route('voting.verificationform');
    }
    
    public function showVerificationForm(Request $request)
    {
        return view('votingverify');
    }
    
    public function verify(Request $request)
    {
        $user_r = $request->session()->get('voting.user');
        $method = $request->session()->get('voting.verification');
        
        if(!($user_r instanceof ExtUserResource) or empty($method)) {
            return redirect()->route('voting.home')->withErrors(['failure' => 'Your session has expired.']);
        }
        
        $attrs = $user_r->toArray(null);
        if($method == 'sms') {
            $this->validateToken($request, $attrs['phone']);
            # remove unverified mail contact
            $user_r->resource['attributes']['email'] = null;
        } else {
            $voting_user = new VotingUser($attrs['email']);
            $this->activationMgr()->validateToken($voting_user, $request->only('token'));
            # remove unverified phone contact
            $user_r->resource['attributes']['phone'] = null;
        }

        # now the user is authenticated, either by external IdP or by validation token
        #  - if the user already exists, login and proceed to code
        #  - if the user does not exist, create one (possibly with external id) and proceed to code
        # either way on exit we should have a valid user account (which kind of duplicates the password reset path/webflow)
        $source = ExtSource::where('type', 'Internal')->get()->first();
        $users = $this->findUserByExtResource($source, $user_r);
        if($users->count() == 0) {
            # this user does not exist yet, create
            $user = $this->createUser($source, $user_r);
            if(false === $user) {
                return redirect()
                ->route('voting.home')
                ->withInput($request->all())
                ->withErrors(['failure' => __('User is already registered.')]);
            } else  if(empty($user)) {
                return redirect()
                    ->route('voting.home')
                    ->withInput($request->all())
                    ->withErrors(['failure' => __('Registration failed.')]);
            }
        } else if ($users->count() == 1){
            # we have found a user
            $user = $users->first();
        }
        $ldap_user = $this->ldap_mgr->findUser($user);
        # convert to App\User (see middleware ExternalIdpAuthenticateSession)
        $user = new User([], $ldap_user->getQuery());
        $user->setRawAttributes($ldap_user->getAttributes());

        Auth::login($user);
        
        if($this->voting_code_mgr->hasActiveVotingCode($user->getDatabaseUser())) {
            return redirect()->route('voting.show');
        } else {
            return redirect()->route('voting.get');
        }
        
    }
    
    public function registerExt(Request $request, $client)
    {
        # we get here only after ExternalIdpAuthenticate middleware does its job and establishes
        # external identity in appropriate guard
        if(Auth::user() instanceof \App\User) {
            # external user with registered identity
            return redirect()->route('home');
        }
        return redirect()->route('voting.home', [ 'client ' => $client ]);
    }
    
    public function showCode(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        $code = $this->voting_code_mgr->getActiveVotingCode($user);
        $login = Auth::user()->getFirstAttribute('uid');
        $idcard = "";
        if(!empty($user->uris)) {
            $pos = $user->uris
            ->search(function ($item, $key) {
                return strstr($item, 'urn:mestouvaly:idcard:');
            });
                if($pos !== false) {
                    $val = $user->uris->get($pos)->uri;
                    $idcard = substr($val, strrpos($val, ':') + 1);
                }
                
        }
        return view('votingcode', ['user' => $user, 'login' => $login, 'idcard' => $idcard, 'code' => $code]);
    }
    
    public function getCode(Request $request) 
    {
        $user = Auth::user()->getDatabaseUser();
        return view('votingform', [ 'user' => $user ]);
    }
    
    public function declare(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        if($request->get("consent_check", "no") != "agree") {
            return back()
                ->withErrors(['failure' => 'You have to agree with the declaration to obtain voting code.']);
        }
        if(!$this->voting_code_mgr->assignVotingCode($user)) {
            return back()
                ->withErrors(['failure' => 'Could not assign new voting code']);
        }
        return redirect()->route('voting.show')->with(['status' => __('Declaration accepted.')]);
    }

    protected function createUser(ExtSource $source, ExtUserResource $data)
    {
        # create UserExt and activate 
        $user_e = $this->reg_mgr->createUserExt($data);
        if(false == $user_e) {
            return false;
        }
        # this fires en event starting the identity build process 
        $this->reg_mgr->activateUser($user_e);
        event(new Registered($user_e));
        return $this->reg_mgr->getRegisteredUser($user_e);
    }
    
    protected function validator(array $data)
    {
        return Validator::make($data, [
            #'g-recaptcha-response' => 'required|recaptcha',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            //'birth_date' => 'sometimes|required_without:phone|date',
            'birth_year' => 'required|date_format:Y',
            'email' => 'sometimes|nullable|required_without:phone|required_if:preferred,email|string|email|max:255',
            'phone' => 'sometimes|nullable|required_without:email|required_if:preferred,sms|string|phone|max:255',
        ]);
    }
    
    protected function activationMgr() 
    {
        return app()->makeWith('App\Interfaces\ActivationManager', [
            'tokens' => $this->broker()->getRepository()
        ]);
    }
}

