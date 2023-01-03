<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;

class MfaController extends Controller
{
    public function __construct() {
        $this->middleware(['auth', 'model'])->except(['showGauth']);    
    }
    
    public function showOverview(Request $request) {
        return view('mfa/overview');
    }
    
    public function showGauth(Request $request) {
        redirect()->setIntendedUrl(route('mfa.home'));
        return redirect()
            ->action([LoginController::class, 'stepup'], ['method' => 'mfa-gauth']);
        
        return view('mfa/gauth');
    }
}
