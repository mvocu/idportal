<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App\Auth\ActivationUser;
use Illuminate\Support\Facades\App;

trait SendsAccountActivationEmail {

    public function showActivationForm()
    {
        return view('auth.activation');
    }

    public function sendActivationLink(Request $request)
    {
        $user = new ActivationUser($request->input('email'));
        $activation_mgr = app()->makeWith('App\Interfaces\ActivationManager', [
            'tokens' => $this->broker()
                ->getRepository()
        ]);
        $activation_mgr->sendActivationLink($user);
    }
    
}

