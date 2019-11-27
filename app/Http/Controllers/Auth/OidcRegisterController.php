<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Interfaces\UserExtManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\ExtSource;
use App\Http\Resources\ExtUserResource;
use Illuminate\Support\MessageBag;

class OidcRegisterController extends Controller
{
    protected $user_ext_mgr;
    protected $ext_source_mgr;
    
    protected $redirectTo = "/login";
    
    public function __construct(ExtSourceManager $ext_source_mgr, UserExtManager $user_ext_mgr) {
        $this->middleware(['oidc'])->only('show');
        $this->middleware(['guest','oidc'])->only('register');
        $this->middleware(['oidc', 'auth'])->only('add');
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
    }
    
    public function show(Request $request, $client) {
        $user = Auth::guard($client)->user();
        $auth_user = Auth::user();
        
        $idp_s = $this->getExtSource($client);
        $euser = $this->user_ext_mgr->getUser($idp_s, $user->getResource());
        $attributes =  $this->user_ext_mgr->mapUserAttributes($idp_s, $user->getResource());
        $data = $user->getAttributes();
        if(is_null($auth_user)) {
            $validator = $this->validator($data);
            $validator->passes();
            if(!is_null($euser)) {
                return view('auth.oidc', [ 'idp' => $client, 'attributes' => $attributes, 
                    'invalid' => $validator->errors()
                        ->add('failure', __("User already registered. Please log in and add external identity to your account."))
                ]);
            }
        } else {
            if(!is_null($euser)) {
                return view('auth.oidc', [ 'idp' => $client, 'attributes' => $attributes,
                    'invalid' => new MessageBag(['failure' => __("Identity already registered")])
                ]);
            }
        }
        return view('auth.oidc', [ 
            'idp' => $client, 
            'attributes' => $attributes, 
            'invalid' => is_null($auth_user) ? $validator->errors() : new MessageBag(), 
            'user' => $auth_user 
        ]);   
    }

    public function register(Request $request, $client) {
        $user = Auth::guard($client)->user();
        $data = $user->getAttributes();
        $this->validator($data)->validate();
        $idp_s = $this->getExtSource($client);
        
        $user = $this->create($idp_s, $user->getResource());
        if(false === $user) {
            return redirect('/register')
            ->withInput($data)
            ->withErrors(['email' => __('User already registered.')]);
        }
        
        event(new Registered($user));
        
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
        $validator = $this->validator($data);
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
        if(null == $this->user_ext_mgr->activateUser($idp_s, $eresource)) {
            return redirect()->route('home')
            ->withErrors(['failure' => __('Failed to activate external identity')]);
        }
        return redirect()->route('home')
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
    
    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        return redirect()->route('password.request');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'given_name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contact,email',
            'phone_number' => 'required|string|phone|max:255|unique:contact,phone',
            'phone_number_verified' => 'required|in:true,1',
            'email_verified' => 'required|in:true,1'
        ]);
    }

    protected function getExtSource($client) 
    {
        return ExtSource::where([
            ['name', '=', $client],
            ['identity_provider', '=', 1]
        ])->get()->first();
    }
}

