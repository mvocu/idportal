<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $this->validate($request, $this->rules());
        $pw = $request->input('password');
        
        try {
            $user = Auth::user();
            $user->setPasswordAttribute($pw);
            $user->save();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['failure' => __('Error changing password: :msg', ['msg' => $e->getMessage()])]);
        }
        
        return redirect()
            ->route('home')
            ->with(['status' => __('Password changed.')]);
    }
    
    protected function rules()
    {
        return [
          'password' => ['required', 'confirmed'],  
        ];
    }
}
