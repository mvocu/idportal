<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use App\Interfaces\ActivationManager;
use App\Models\Database\UserExt;
use App\Auth\ActivationUser;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Contracts\Support\MessageBag;

trait ActivatesAccount {

    use RedirectsUsers;
    
    public function showActivateForm(Request $request, $id, $token = null)
    {
        return view('auth.activate')->with(
            ['token' => $token, 'uid' => $id]
            );
    }

    public function activate(Request $request)
    {
        $ldap_user = null;
        $errors = null;
        
        // validate input fields
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        // validate token against the token database
        $activation_mgr = $this->activationManager();
        $user = new ActivationUser($request->input('uid'));
        $activation_mgr->validateToken($user, $request->only('token'));
        

        // activate user
        $user_ext = $activation_mgr->activateAccount($user);
        if($user_ext == null) {
            return $this->sendActivationFailedResponse($request, 'activation-not-found');
        }
            
        // wait for the async user creation
        for($count = 0; $count < 30 && $ldap_user == null && $errors == null; $count++) {
            sleep(1);
            $new_user = $this->checkAccount($user_ext->refresh());
            if(!empty($new_user)) {
                $ldap_user = $new_user;
            }
        }

        if($ldap_user == null) {
            return $this->sendActivationFailedResponse($request, ($errors == null) ? 'activation-failed' : $errors);
        }
        
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        if(!$this->resetPassword($ldap_user, $request->input('password'))) {
            return redirect()->route('password.request')
                ->withErrors(['failure' => trans('reset-failed')]);
        }
        
        
        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $this->sendActivationResponse($request, 'activation-done');
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'uid' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ];
    }
    
    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }
    
    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
            );
    }
    
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
    }
    
    protected function checkAccount(UserExt $user_ext) 
    {
        return null;
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendActivationResponse(Request $request, $response)
    {
        return redirect($this->redirectPath())
        ->with('status', trans($response));
    }
    
    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendActivationFailedResponse(Request $request, $response)
    {
        if($response instanceof MessageBag) {
            return redirect('/register')
                ->withInput($request->only('uid'))
                ->withErrors($response);
        } else {
            return redirect('/register')
                ->withInput($request->only('uid'))
                ->withErrors(['failure' => trans($response)]);
        }
    }
    
    protected function activationManager() : ActivationManager
    {
        return app()->makeWith('App\Interfaces\ActivationManager', [
            'tokens' => $this->broker()
            ->getRepository()
        ]);
    }
    
    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    protected function broker()
    {
        return Password::broker();
    }
    
    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}

