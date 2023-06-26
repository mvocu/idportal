<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;


class LoginController extends Controller
{
    
    public function __construct()
    {
    }
        
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request, $method = null)
    {
        if(Auth::attempt($method ? ['delegate' => $method ]: [])) {
            $request->session()->regenerate();
            
            return redirect()->intended();
        }
        
        return back()->withErrors([]);
    }
    
    public function stepup(Request $request, $method)
    {
        if(Auth::attempt(['mfa' => $method])) {
            $request->session()->regenerate();
            
            return redirect()->intended();
        }
        
        return back()->withErrors([]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if(1 || Auth::hasUser()) {
            Auth::logout(url()->current());
        }
        
        return $request->wantsJson()
        ? new JsonResponse([], 204)
        : redirect()->intended();
    }
    
    
}

