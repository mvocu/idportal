<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordController extends Controller
{

    public function __construct() {
        $this->middleware(['auth', 'model']);
    }
    
    public function showPasswordForm(Request $request) {
        return view('password.form');
    }
    
}
