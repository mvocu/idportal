<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Database\UserExt;
use Illuminate\Support\HtmlString;

class UserExtController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('group:administrators');
    }
    
    public function listUsers(Request $request)
    {
        $users = UserExt::with(['attributes', 'attributes.attrDesc', 'extSource', 'user'])->get();
        
        $table = tableView($users)
        ->column('Id', 'id')
        ->column(__('User'), function(UserExt $user) {
            return new HtmlString(view('components.userlink', ['user' => $user->user, 'name' => $user->user_id])->render());
            // return '<a href="'.route('admin.user.show', ['user' => $user->user]).'">'.$user->user_id.'</a>';
        })
        ->column(__('Identificator'), 'login')
        ->column(__('External source'), 'extSource.name') 
        ->childDetails(function (UserExt $user) {
            return view('admin.part.userextdetail', ['embed' => true, 'id' => $user->id, 'user' => $user]);
        })
        ->setTableClass('table table-striped')
        ->useDataTable();
        
        return view('admin.listextusers', ['table' => $table ]);
    }
    
    public function showUser(Request $request, UserExt $user)
    {
        return view('admin.userextdetail', ['embed' => false, 'user' => $user]);
    }
}
