<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SetADPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['socialite:socialite']);
    }
    
    /**
     * Show password form
     * 
     */
    public function showPasswordForm(Request $request) 
    {
        return view('password');
    }
    
    /**
     * Set new AD password
     * 
     */
    public function changePassword(Request $request)
    {
        return redirect('/home');
    }
}
