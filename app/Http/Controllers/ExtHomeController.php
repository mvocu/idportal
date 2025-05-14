<?php
namespace App\Http\Controllers;

use App\Interfaces\LdapConnector;
use App\Interfaces\UserExtManager;
use App\Interfaces\VotingCodeManager;
use App\Models\Database\ExtSource;
use App\Services\ConsentManager;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\UserManager;
use App\Traits\FindsExternalAccount;
use Illuminate\Http\Request;
use App\User;

class ExtHomeController extends Controller
{
    
    use FindsExternalAccount;
    
    protected $user_mgr;
    
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
        UserManager $user_mgr,
        UserExtManager $user_ext_mgr,
        ConsentManager $consent_mgr,
        VotingCodeManager $voting_code_mgr,
        LdapConnector $ldap_mgr)
    {
        $this->user_mgr = $user_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->consent_mgr = $consent_mgr;
        $this->voting_code_mgr = $voting_code_mgr;
        $this->ldap_mgr = $ldap_mgr;
        $this->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'auth:MojeID,eIdentita,web']);
    }
    
    public function index(Request $request, $client = null) 
    {
        if(Auth::user() instanceof \App\User && !empty(Auth::user()->getAuthPassword())) {
            return redirect()->route('home');
        }
        if(!Auth::user() instanceof \App\User) {
            # having logged in using external identity, we have to get to the actual user differently
            #$user = Auth::user()->getDatabaseUser();
            if(empty($client)) {
                $client = Auth::guard()->getClient();
            }
            $users = $this->findUserByExtIdentity(Auth::user(), $client);
            if($users->count() == 1) {
                $user = $users->first();
            } else {
                $user = null;
            }
        } else {
            $user = Auth::user()->getDatabaseUser();
        }
        # $user has to be instance of database model
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
            $ldap_user = $this->ldap_mgr->findUser($user);
            # convert to App\User (see middleware ExternalIdpAuthenticateSession) 
            $appuser = new User([], $ldap_user->getQuery());
            $appuser->setRawAttributes($ldap_user->getAttributes());
            $ldap_user = $appuser;
        } else {
            $ldap_user = null;
        }
        return view('exthome', [
            'user' => $ldap_user,
            'accounts' => $accounts,
            'children' => empty($ldap_user) ? collect([]) : $this->ldap_mgr->listChildren($ldap_user),
            'voting' => empty($user) ? false : $this->voting_code_mgr->hasActiveVotingCode($user),
            'expires' => empty($user) ? "" : $this->consent_mgr->expiresSoon($user)
        ]);
    }
    
}

