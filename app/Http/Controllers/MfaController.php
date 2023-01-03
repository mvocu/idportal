<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MfaController extends Controller
{
    public function __construct() {
        $this->middleware(['auth', 'model']);    
    }
    
    public function showOverview(Request $request) {
        return view('mfa/overview');
    }
    
    public function showGauth(Request $request) {
        Auth::guard()->attempt(['acr_values' => 'mfa-gauth']);
        return view('mfa/gauth');
    }
}
