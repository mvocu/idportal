<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Cas\GauthRecord;
use App\Interfaces\MfaManager;
use App\Models\Cas\WebAuthnDevice;

class MfaController extends Controller
{
    public function __construct() {
        $this->middleware(['auth', 'model']);    
    }
    
    public function showOverview(Request $request, MfaManager $mfa) {
        $user = Auth::user();
        $policy = $mfa->getPolicy($user);
        $gauth = GauthRecord::forUser($user->getId());
        $webauthn = WebAuthnDevice::forUser($user->getId());
        $sms = $user->getAuthUser()->mobile; 
        return view('mfa/overview', [
            'policy' => $policy, 
            'gauth' => $gauth, 
            'webauthn' => $webauthn,
            'sms' => $sms,
        ]);
    }
    
    public function showGauth(Request $request) {
        redirect()->setIntendedUrl(route('mfa.home'));
        return redirect()
            ->action([LoginController::class, 'stepup'], ['method' => 'mfa-gauth']);
        
        return view('mfa/gauth');
    }
    
    public function showWebAuthn(Request $request) {
        redirect()->setIntendedUrl(route('mfa.home'));
        return redirect()
        ->action([LoginController::class, 'stepup'], ['method' => 'mfa-webauthn']);
        
        return view('mfa/webauthn');
    }

    public function showSms(Request $request) {
        redirect()->setIntendedUrl(route('mfa.home'));
        return redirect()
        ->action([LoginController::class, 'stepup'], ['method' => 'mfa-simple']);
        
        return view('mfa/sms');
    }
    
}
