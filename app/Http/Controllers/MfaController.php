<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MfaController extends Controller
{
    public function __construct() {
        $this->middleware(['auth', 'model']);    
    }
    
    public function showOverview(Request $request) {
        return view('mfa/overview');
    }
}
