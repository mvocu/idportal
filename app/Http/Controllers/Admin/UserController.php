<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use App\Http\Controllers\Controller;
use App\Models\Database\User;
use App\Models\Database\Contact;
use App\Interfaces\LdapConnector;
use App\Interfaces\VotingCodeManager;
use App\Models\Database\Uri;
use Carbon\Carbon;

class UserController extends Controller
{
    protected $ldapc;

    protected $voting_code_mgr;
    
    public function __construct(LdapConnector $ldapc, VotingCodeManager $voting_code_mgr)
    {
        $this->ldapc = $ldapc;
        $this->voting_code_mgr = $voting_code_mgr;
        $this->middleware('auth');
        $this->middleware('group:administrators');
    }
    
    public function listUsers(Request $request)
    {
        if($request->isMethod('POST')) {
            $search = $request->input('search', '');
            $internal = $request->input('internal', 0);
            $voter = $request->input('voting', 0);
            $query = User::with(['phones', 'emails', 'accounts', 'accounts.attributes', 'accounts.extSource']);
            if($internal) {
                $query = $query->doesntHave('accounts');
            }
            if($voter) {
                $query = $query->has('votingCodes');
            }
            if(!empty($search)) {
                $query = $query->where(function($query) use ( $search )
                    {
                        $query = $query->where('identifier', 'like', $search);
                        foreach(['first_name', 'last_name', 'birth_date'] as $column) {
                            $query = $query->orWhere($column, 'like', $search);
                        }
                        foreach(['street', 'email', 'phone', 'databox', 'bank_account'] as $column) {
                           $query = $query->orWhereHas('contacts', function($subquery) use ($column, $search) {
                                $subquery->where($column, 'like', $search); 
                           });
                        }   
                        $query = $query->orWhereHas('contacts', function($subquery) use ($search) {
                            $subquery->where('uri', 'like', '%'.$search);
                        });
                    });
            }
            $users = $query->latest()->get();
        } else {
            $users = User::with(['phones', 'emails', 'accounts', 'accounts.attributes', 'accounts.extSource'])
            ->latest()
            ->get();
        }
        
        $table = tableView($users)
            ->column('Id', function(User $user) {
                return new HtmlString(view('components.userlink', ['user' => $user, 'name' => $user->id])->render());
            })
            ->column(__('First name'), 'first_name')
            ->column(__('Last name'), 'last_name')
            ->column(__('Phone'), function(User $user) {
                return $user->phones->pluck('phone')->implode('<br/>');
            })
            ->column(__('E-mail'), function(User $user) {
                return $user->emails->pluck('email')->implode('<br/>');
            })
            #->childDetails(function (User $user) {
            #    return view('admin.part.userdetail', ['id' => $user->id, 'user' => $user]);
            #})
            ->setTableClass('table table-striped')
            ->useDataTable();
        
        return view('admin.listusers', ['table' => $table]);
    }
    
    public function showUser(Request $request, User $user)
    {
        $ldapuser = $this->ldapc->findUser($user);
        $idcard = $user->getIdCard();
        return view('admin.userdetail', ['id' => $user->id, 'user' => $user, 'ldapuser' => $ldapuser, 
            'haspw' => $ldapuser ? $this->ldapc->hasPassword($ldapuser) : false,
            'lock' => $ldapuser ? $this->ldapc->isUserLocked($ldapuser) : false,
            'voting_code' => $this->voting_code_mgr->getActiveVotingCode($user),
            'idcard' => $idcard,
        ]);
    }
    
    public function newUser(Request $request) {
        $table = null;
        $users = $request->session()->get('conflicts');
        
        if($users && !$users->isEmpty()) {
            $table = tableView($users)
            ->column('Id', function(User $user) {
                return new HtmlString(view('components.userlink', ['user' => $user, 'name' => $user->id])->render());
            })
            ->column(__('First name'), 'first_name')
            ->column(__('Last name'), 'last_name')
            ->column(__('Date of birth'), 'birth_date')
            ->setTableClass('table table-striped')
            ->useDataTable();
        }
            
        return view('admin.newuser', [ 'table' => $table ]);
    }
    
    public function createUser(Request $request) {
        $data = $request->all();
        $conflicts = $request->input('conflicts', 'check');
        $ndata = $data;
        if(array_key_exists('idcard', $data)) {
            $idcard = 'urn:mestouvaly:idcard:' . $data['idcard'];
            $ndata['idcard'] = $idcard;
        }
        $data['birthdate'] = '01/01/' . $data['birth_year'];
        $this->validator($ndata)->validate();

        # first try to find pre-existing user
        $users = User::where([
            ['first_name', '=', $data['firstname']],
            ['last_name', '=', $data['lastname']],
            ['birth_date', '=', new Carbon($data['birthdate'])]
        ])->get();
        if($conflicts == 'check' && !$users->isEmpty()) {
            $request->session()->flashInput($data);
            return redirect()->back()->withErrors([
                'failure' => 'Another conflicting account found'
            ])->with('conflicts', $users);
        }
        
        $user = new User([
            'first_name' => $data['firstname'],
            'last_name' => $data['lastname'],
            'birth_date' => $data['birthdate'],
        ]);
        $user->identifier = Uuid::uuid4();
        $user->trust_level = 1;
        $user->export_to_ldap = 0;
        $user->save();
        if(!empty($idcard)) {
            $uri = new Uri(['uri' => $idcard]);
            $user->uris()->save($uri);
        }

        if(!$this->voting_code_mgr->assignVotingCode($user)) {
            return back()
            ->withErrors(['failure' => 'Could not assign new voting code']);
        }
        
        return redirect()->route('admin.user.show.code', ['user' => $user->refresh() ]);
    }

    public function showVotingCode(Request $request, User $user) {
        $idcard = $user->getIdCard();
        return view('admin.votingcode', [ 'user' => $user, 'idcard' => $idcard,
            'code' => $this->voting_code_mgr->getActiveVotingCode($user),
        ]);
    }
    
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'birth_year' => 'required|date_format:Y',
            'idcard' => 'sometimes|nullable|string|max:255|unique:contact,uri',
        ]);
    }
    
}
