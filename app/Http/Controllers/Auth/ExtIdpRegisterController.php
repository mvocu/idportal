<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Interfaces\UserExtManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use App\Http\Resources\ExtUserResource;
use Illuminate\Support\MessageBag;

class ExtIdpRegisterController extends Controller
{
    protected $user_ext_mgr;
    protected $ext_source_mgr;
    
    protected $redirectTo = "/login";
    protected $casLogoutUrl;
    
    public function __construct(ExtSourceManager $ext_source_mgr, UserExtManager $user_ext_mgr) {
        $this->middleware(['eidp'])->only('show');
        $this->middleware(['guest','eidp'])->only('register');
        $this->middleware(['eidp', 'auth'])->only('add');
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->casLogoutUrl = Config::get('cas')['logout_url'];
    }
    
    public function show(Request $request, $client) {
        $user = Auth::guard($client)->user();
        $auth_user = Auth::user();
        
        $idp_s = $this->getExtSource($client);
        $euser = $this->user_ext_mgr->getUser($idp_s, $user->getResource());
        $attributes =  $this->user_ext_mgr->mapUserAttributes($idp_s, $user->getResource());
        $data = $user->getAttributes();

        if(is_null($auth_user)) {
            if(!is_null($euser)) {
                # this external identity already has been registered, redirect home 
                return redirect()->route('home')
                        ->with('status', __('Your external account has already been registered.'));
            }
            $validator = $this->validator($data, $user);
            if(!$validator->passes()) {
                $failed = $validator->failed();
                if(array_key_exists('email', $failed) && array_key_exists('Unique', $failed['email']) ||
                    array_key_exists('phone_number', $failed) &&  array_key_exists('Unique', $failed['phone_number'])) {
                        return view('auth.eidp', [ 
                            'idp' => $client, 
                            'attributes' => $attributes,
                            'resource' => $user->getResource(),
                            'invalid' => $validator->errors()
                            ->add('failure', __("There already is an account using these contacts. Please login and add external identity."))
                        ]);
                }
                return view('auth.eidp', [ 
                    'idp' => $client, 
                    'attributes' => $attributes, 
                    'resource' => $user->getResource(),
                    'invalid' => $validator->errors()
                        ->add('failure', __("External account can not be registered, it contains invalid data."))
                ]);
            }
        } else {
            if(!is_null($euser)) {
                return view('auth.eidp', [ 
                    'idp' => $client, 
                    'attributes' => $attributes,
                    'resource' => $user->getResource(),
                    'invalid' => new MessageBag(['failure' => __("Identity already registered")])
                ]);
            }
        }
        return view('auth.eidp', [ 
            'idp' => $client, 
            'attributes' => $attributes, 
            'invalid' => new MessageBag(), 
            'user' => $auth_user,
            'resource' => $user->getResource(),
        ]);   
    }

    public function register(Request $request, $client) {
        $user = Auth::guard($client)->user();
        $data = $user->getAttributes();
        $this->validator($data, $user)->validate();
        $idp_s = $this->getExtSource($client);
        
        $user = $this->create($idp_s, $user->getResource());
        if(false === $user) {
            return redirect('/register')
            ->withInput($data)
            ->withErrors(['email' => __('User already registered.')]);
        }
        
        event(new Registered($user));
        
        // wait for the async user creation
        $ldap_user = null;
        for($count = 0; $count < 5 && $ldap_user == null; $count++) {
            sleep(1);
            $new_user = $this->checkAccount($user->refresh());
            if(!empty($new_user)) {
                $ldap_user = $new_user;
            }
        }
        
        return $this->registered($request, $user)
        ?: redirect($this->redirectTo);
        
    }

    public function add(Request $request, $client) 
    {
        $user = Auth::guard($client)->user();
        $auth_user = Auth::user()->getDatabaseUser();
        if(is_null($auth_user)) {
            return redirect()->route('home')
                ->withErrors(['failure' => __('Target user not found')]);
        }
        $data = $user->getAttributes();
        $validator = $this->validator($data, $user);
        if($validator->fails()) {
            $data = $validator->valid();
        }
        $idp_s = $this->getExtSource($client);
        $eresource = $user->getResource(false);
        $euser = $this->create($idp_s, $eresource);
        if(false === $euser) {
            return redirect()->route('home')
                ->withErrors(['failure' => __('Failed to add external identity')]);
        }
        $euser->user()->associate($auth_user);
        $euser->save();
        if(null == $this->user_ext_mgr->activateUserByData($idp_s, $eresource)) {
            return redirect()->route('home')
            ->withErrors(['failure' => __('Failed to activate external identity')]);
        }

        // wait for the async user creation
        $ldap_user = null;
        for($count = 0; $count < 5 && $ldap_user == null; $count++) {
            sleep(1);
            $new_user = $this->checkAccount($euser->refresh());
            if(!empty($new_user)) {
                $ldap_user = $new_user;
            }
        }
        
        return redirect($this->casLogoutUrl . '?service=' . route('home'))
            ->with(['status' => __('External identity added')]);
    }
    
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return
     */
    protected function create(ExtSource $source, ExtUserResource $resource)
    {
        $user = $this->user_ext_mgr->getUser($source, $resource);
        if($user != null) {
            $this->user_ext_mgr->updateUserWithAttributes($source, $user, $resource);
            return $user;
        }
        $user = $this->user_ext_mgr->createUserWithAttributes($source, $resource);
        return $user;
    }
    
    protected function checkAccount(UserExt $user)
    {
        return null;    
    }
    
    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        return redirect($this->casLogoutUrl . '?service=' . route('password.request'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $user)
    {
        return Validator::make($data, $user->getValidatorRules());
    }

    protected function getExtSource($client) 
    {
        return ExtSource::where([
            ['name', '=', $client],
            ['identity_provider', '=', 1]
        ])->get()->first();
    }
}

