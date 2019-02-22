<?php

namespace App\Http\Controllers;

use Adldap\Laravel\Facades\Adldap;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user()->getDatabaseUser();
        $accounts = array();
        foreach(ExtSource::all() as $source) {
            $name = $source->name;
            $tag = Str::kebab(Str::lower(Str::ascii($name)));
            $accounts[$source->id] = [ 'name' => $name, 'tag' => $tag ];
        }
        if($user != null) {
            foreach($user->accounts as $account) {
                $data = $this->user_ext_mgr->getUserResource($account)->toArray(null);
                $accounts[$account->extSource->id]['phones'] = $data['phones'];
                $accounts[$account->extSource->id]['emails'] = $data['emails'];
            }
        }
        return view('home', ['user' => Auth::user(), 'accounts' => $accounts ]);
    }
}
