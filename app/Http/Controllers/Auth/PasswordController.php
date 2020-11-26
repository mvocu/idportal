<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Interfaces\LdapConnector;
use App\User;

class PasswordController extends Controller
{
    
    public function __construct(LdapConnector $ldap_mgr)
    {
        $this->ldap_mgr = $ldap_mgr;
        $this->middleware(['auth.oidc:MojeID', 'auth']);
    }

    public function showPasswordForm(Request $request, User $target = null) 
    {
        if(empty($target)) {
            $target = Auth::user();
        }
        return view('auth.passwords.change', [ 'target' => $target ]);
    }
    
    public function changePassword(Request $request, User $target = null) 
    {
        if(empty($target)) {
            $target = Auth::user();
        }
        $current = Auth::user();
        if(!$current->can('changepw', $target)) {
            return back()
            ->withErrors(['failure' => __('You are not authorized.')]);
        } 
        $this->validate($request, $this->rules());
        try {
            $password = $request->input('password');
            $this->ldap_mgr->changePassword($target, $password);
            if($target->getAuthIdentifier() == $current->getAuthIdentifier()) {
                $current->rememberPassword($password);
                $this->guard()->login($current);
            }
        } catch(\Exception $e) {
            return back()
                ->withErrors(['failure' => __('Error changing password: :msg', ['msg' => $e->getMessage()])]);
        }
        return redirect()
            ->route('home')
            ->with(['status' => __('Password changed.')]);
    }

    protected function rules()
    {
        return [
            'password' => 'required|confirmed|min:6',
        ];
    }
}
