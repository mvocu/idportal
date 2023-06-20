<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserExtController extends Controller
{
    public function __construct() {
        
    }
    
    public function showOverview(Request $request) {
        return view('ext.overview');
    }
        
}
