<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use App\Http\Controllers\Controller;
use App\Models\Database\User;
use App\Interfaces\LdapConnector;

class UserController extends Controller
{
    protected $ldapc;
    
    public function __construct(LdapConnector $ldapc)
    {
        $this->ldapc = $ldapc;
        $this->middleware('auth');
        $this->middleware('group:administrators');
    }
    
    public function listUsers(Request $request)
    {
        $users = User::with(['phones', 'emails', 'accounts', 'accounts.attributes', 'accounts.extSource'])->get();
        
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
        
        return view('admin.listusers', ['table' => $table ]);
    }
    
    public function showUser(Request $request, User $user)
    {
        $ldapuser = $this->ldapc->findUser($user);
        return view('admin.userdetail', ['id' => $user->id, 'user' => $user, 'ldapuser' => $ldapuser, 
            'haspw' => $ldapuser ? $this->ldapc->hasPassword($ldapuser) : false,
            'lock' => $ldapuser ? $this->ldapc->isUserLocked($ldapuser) : false,
        ]);
    }
    
}
