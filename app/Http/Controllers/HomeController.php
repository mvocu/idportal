<?php

namespace App\Http\Controllers;

use Adldap\Laravel\Facades\Adldap;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;
use App\Interfaces\ConsentManager;
use Adldap\AdldapInterface;
use App\Interfaces\LdapConnector;
use App\Interfaces\VotingCodeManager;

class HomeController extends Controller
{
    protected $user_ext_mgr;
    
    protected $consent_mgr;
    
    protected $ldap_mgr; 
    
    protected $voting_code_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserExtManager $user_ext_mgr, 
        ConsentManager $consent_mgr,
        VotingCodeManager $voting_code_mgr,
        LdapConnector $ldap_mgr)
    {
        $this->user_ext_mgr = $user_ext_mgr;
        $this->consent_mgr = $consent_mgr;
        $this->voting_code_mgr = $voting_code_mgr;
        $this->ldap_mgr = $ldap_mgr;
        $this->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'auth:MojeID,eIdentita,web']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!Auth::user() instanceof \App\User) {
            #return redirect()->route('register.eidp', ['client' => Auth::guard()->getClient()])
            #    ->withErrors(['failure' => __('External identity is not registered. Please register your account here.')]);
            return redirect()->route('ext.home', ['client' => Auth::guard()->getClient()])
            ->withErrors(['failure' => __('External identity is not registered.')]);
        }
        if(empty(Auth::user()->getAuthPassword())) {
            # the user is in registration process, no database or ldap record present yet
            return redirect()->route('ext.home')
                ->with(['status' => __('You need to setup your account before proceeding,')]);
        }
        $user = Auth::user()->getDatabaseUser();
        if(!empty($user) && !$this->consent_mgr->hasActiveConsent($user)) {
            return redirect()->route('consent.ask');
        }
        $accounts = array();
        foreach(ExtSource::all() as $source) {
            $name = $source->name;
            $tag = Str::kebab(Str::lower(Str::ascii($name)));
            $editable = $source->editable;
            $idp = $source->identity_provider;
            $accounts[$source->id] = [ 
                'name' => $name, 'tag' => $tag, 
                'editable' => $editable , 'creatable' => $source->type != 'Internal', 
                'idp' => $idp];
        }
        if($user != null) {
            foreach($user->accounts as $account) {
                $accounts[$account->extSource->id]['user_ext'] = $account;
                $data = $this->user_ext_mgr->getUserResource($account)->toArray(null);
                if(array_key_exists('phones', $data)) $accounts[$account->extSource->id]['phone'] = $data['phones'][0]['phone'];
                if(array_key_exists('emails', $data)) $accounts[$account->extSource->id]['email'] = $data['emails'][0]['email'];
            }
        }
        return view('home', [
            'user' => Auth::user(), 
            'accounts' => $accounts, 
            'children' => $this->ldap_mgr->listChildren(Auth::user()), 
            'voting' => empty($user) ? false : $this->voting_code_mgr->hasActiveVotingCode($user),
            'expires' => empty($user) ? "" : $this->consent_mgr->expiresSoon($user)
        ]);
    }
}
