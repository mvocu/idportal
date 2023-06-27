<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Http\Controllers\Auth\LoginController;
use App\Interfaces\MfaManager;
use App\Models\Cas\MfaPolicy;
use App\Auth\OidcUser;

class MfaController extends Controller
{
    protected $mfa;
    
    public function __construct(MfaManager $mfa) {
        $this->mfa = $mfa;
        $this->middleware(['auth', 'model']);    
    }
    
    public function showOverview(Request $request) {
        $user = Auth::user();
        if($user instanceof OidcUser) {
            return redirect()->route('ext.home');
        }
        $ldap_user = $user->getLdapUser();
        $policy = $this->mfa->getPolicy($ldap_user);
        $gauth = $this->mfa->getGauthCredentials($ldap_user);
        $webauthn = $this->mfa->getWebAuthnDevices($ldap_user);
        $trusted = $this->mfa->getTrustedDevices($ldap_user);
        $sms = $this->mfa->getPagerNumber($ldap_user); 
        return view('mfa/overview', [
            'policy' => $policy, 
            'gauth' => $gauth, 
            'webauthn' => $webauthn,
            'sms' => $sms,
            'trusted' => $trusted
        ]);
    }
    
    public function showPolicy(Request $request) {
        $user = Auth::user();
        $policy = $this->mfa->getPolicy($user->getLdapUser());

        return view('mfa/policy', [ 'user' => $user, 'policy' => $policy ]);
    }
    
    public function setPolicy(Request $request, MfaManager $mfa) {
        $user = Auth::user();
        $policy = new MfaPolicy($request->input('policy'));
        try {
            $this->mfa->setPolicy($user->getLdapUser(), $policy);
        } catch (Exception $e) {
            return back()->withErrors(['failure' => __('Error setting MFA policy.')]);
        }
        
        return redirect()->route('mfa.home')->with('status', __('MFA policy saved.'));
    }
    
    public function showGauth(Request $request) {
        $user = Auth::user();
        $gauth = $this->mfa->getGauthCredentials($user->getLdapUser());

        return view('mfa/gauth', [ 'user' => $user, 'gauth' => $gauth ]);
    }
    
    public function deleteGauth(Request $request) {
        $user = Auth::user();

        try {
            $this->mfa->deleteGauthCredentials($user->getLdapUser());
        } catch(Exception $e) {
            return back()->withErrors(['failure' => __('Error removing device records.')]);
        }
        
        return redirect()->back()->with('status', __('All devices removed.'));
    }
    
    public function performGauth(Request $request) {
        redirect()->setIntendedUrl(route('mfa.gauth'));
        return redirect()
            ->action([LoginController::class, 'stepup'], ['method' => 'mfa-gauth']);
    }
    
    public function showWebAuthn(Request $request) {
        $user = Auth::user();
        $webauthn = $this->mfa->getWebAuthnDevices($user->getLdapUser());
        
        return view('mfa/webauthn', [ 'user' => $user, 'webauthn' => $webauthn ]);
    }
    
    public function deleteWebAuthn(Request $request) {
        $user = Auth::user();
        
        try {
            $this->mfa->deleteWebAuthnDevices($user->getLdapUser());
        } catch(Exception $e) {
            return back()->withErrors(['failure' => __('Error removing device records.')]);
        }
        
        return redirect()->back()->with('status', __('All devices removed.'));
    }
    
    public function addWebAuthn(Request $request) {
        $user = Auth::user();
        
        return view('mfa/addwebauthn', [ 'user' => $user ]);    
    }
    
    public function registerWebAuthn(Request $request) {
        
    }
    
    public function performWebAuthn(Request $request) {
        redirect()->setIntendedUrl(route('mfa.webauthn'));
        return redirect()
            ->action([LoginController::class, 'stepup'], ['method' => 'mfa-webauthn']);
    }

    public function showSms(Request $request) {
        $user = Auth::user();
        $sms = $this->mfa->getPagerNumber($user->getLdapUser());
        return view('mfa/sms', ['sms' => $sms]);
    }
    
    public function performSms(Request $request) {
        redirect()->setIntendedUrl(route('mfa.home'));
        return redirect()
        ->action([LoginController::class, 'stepup'], ['method' => 'mfa-simple']);
    }
    
    public function showTrusted(Request $request) {
        $user = Auth::user();
        $trusted = $this->mfa->getTrustedDevices($user->getLdapUser());
        return view('mfa/trusted', [ 'trusted' => $trusted ]); 
    }

    public function deleteTrusted(Request $request, $device = null) {
        $user = Auth::user();
        try {
            if(empty($device)) {
                $this->mfa->deleteTrustedDevices($user->getLdapUser());
                return redirect()->back()->with('status', __('All devices removed.'));
            } else {
                $this->mfa->deleteTrustedDevices($user->getLdapUser(), $device);
                return redirect()->back()->with('status', __('Device removed.'));
            }
        } catch(Exception $e){
            return back()->withErrors(['failure' => __('Error removing device records.')]);
        }
    }
}
