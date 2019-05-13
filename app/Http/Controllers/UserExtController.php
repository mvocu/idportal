<?php

namespace App\Http\Controllers;

use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;
use App\Models\Database\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserExtController extends Controller
{
    protected $user_ext_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserExtManager $user_ext_mgr)
    {
        $this->user_ext_mgr = $user_ext_mgr;
        $this->middleware('auth');
    }
    
    public function showAddUserExtForm(Request $request, User $user, ExtSource $ext_source)
    {
        $attributes = (new UserResource($user))->getExtAttributes($ext_source->attributes);
        return view('userext', [
            'action' => 'add', 
            'user' => $user, 
            'ext_source' => $ext_source, 
            'attributes' => $attributes
        ]);
    }
}
