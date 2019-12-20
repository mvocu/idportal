<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\ConsentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsentController extends Controller
{

    protected $consent_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConsentManager $consent_mgr)
    {
        $this->consent_mgr = $consent_mgr;
        $this->middleware('auth');
    }
    
    
    public function showConsentForm(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        $this->consent_mgr->setConsentRequested($user, true);
        return view('consent');
    }
    
    public function setConsent(Request $request)
    {
        $user = Auth::user()->getDatabaseUser();
        $this->consent_mgr->setConsent($user, $request->get("consent_check", "no") == "agree");
        return redirect()->route('home')->with(['status' => __('Saved.')]);
    }
    
}

