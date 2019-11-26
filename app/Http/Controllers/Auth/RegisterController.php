<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Traits\AuthorizesBySMS;
use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;
use App\Http\Resources\ExtUserResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Traits\SendsAccountActivationEmail;
use App\Interfaces\ExtSourceManager;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;     /* PROVIDES: showRegistrationForm, register, registered, guard */
                            /* REQUIRES: validator, create */
    use AuthorizesBySMS;    /* PROVIDES: sendAuthorizationToken, validatePhone, validateToken, broker */ 

    use SendsAccountActivationEmail; /* PROVIDES: sendActivationLink */
    
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/auth/activate';

    protected $ext_source_mgr;
    protected $user_ext_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ExtSourceManager $ext_source_mgr, UserExtManager $user_ext_mgr)
    {
        $this->middleware('guest');
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('auth.register', [ 'idp' => $this->ext_source_mgr->listAuthenticators()->pluck('name') ]);
    }
    
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        
        // check token
        $this->validateToken($request);
        
        $user = $this->create($request->all());
        if(false === $user) {
            return redirect('/register')
                ->withInput($request->all())
                ->withErrors(['email' => __('User already registered.')]);    
        }
        
        event(new Registered($user));
        
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
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
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:contact,email',
            'phone' => 'required|string|phone|max:255|unique:contact,phone',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return 
     */
    protected function create(array $data)
    {
        $source = ExtSource::where('type', 'Internal')->get()->first();
        if($source == null) throw new ModelNotFoundException();
        $resource = new ExtUserResource([ 'id' => $data['email'], 'active' => false, 'attributes' => $data ]);
        $user = $this->user_ext_mgr->getUser($source, $resource);
        if($user != null) {
            if($user->active) {
                return false;
            } else {
                $this->user_ext_mgr->updateUserWithAttributes($source, $user, $resource);
                return $user;
            }
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
        // send activation challenge
        $this->sendActivationLink($request);
        
        return redirect()->route('activate.token', [ 'id' => $user->login ])
            ->with('status', __('Activation code was sent to :address', [ 'address' => $request->input('email') ]));
    }
    
}
