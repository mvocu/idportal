<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Support\HtmlString;
use App\Interfaces\SynchronizationManager;

class UserExtController extends Controller
{
    protected $sync_mgr;
    
    public function __construct(SynchronizationManager $sync_mgr)
    {
        $this->middleware('auth');
        $this->middleware('group:administrators,observers');
        $this->sync_mgr = $sync_mgr;
    }
    
    public function listUsers(Request $request, $source = null)
    {
        $request->flash();
        
        if(empty($source) && $request->has('source')) {
            $source = $request->input('source', null);
        }
        
        $query = UserExt::with(['attributes', 'attributes.attrDesc', 'extSource', 'user']);
        if($request->has('missing')) {
            $query = $query->doesntHave('user');
        }
        if(!empty($source) && $source != 'all') {
            $query = $query->where('ext_source_id', $source);
        }
        if($request->has('search') && !empty($value = $request->input('search'))) {
            #$value = $request->input('search');
            $users = $query->whereHas('attributes', function($query) use ($value) {
                $query->where('value', $value);
            })
            ->latest()
            ->get();
        } else {
            $users = $query->latest()->get();
        }

        # filter only records the user has permission to view
        $user = $request->user();
        $users = $users->filter(function ($value, $key) use($user) {
           return $user->can('view', $value); 
        });

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
        
        $sources = ExtSource::all();
        
        return view('admin.listextusers', ['table' => $table, 'sources' => $sources, 'source' => $source ]);
    }
    
    public function showUser(Request $request, UserExt $user)
    {
        return view('admin.userextdetail', ['embed' => false, 'user' => $user]);
    }
    
    public function synchronize(Request $request, $source = null)
    {
        if(empty($source) && $request->has('source')) {
            $source = $request->input('source', null);
        }
        $source = ExtSource::find($source);
        if(empty($source)) {
            return back()
                ->withInput()
                ->withErrors(['failure' => 'Invalid external source']);
        }

        $result = $this->sync_mgr->synchronizeExtSource($source);
        if(empty($result)) {
            return back()
            ->withInput()
            ->withErrors(['failure' => __('Error synchronizing :source', ['source' => $source->name ])]);
        }
        
        return redirect()->route('admin.userext.list.search.source', ['source' => $source])
            ->with('status', __('Successfully synchronized :items items from :name', 
                ['items' => $result->count(), 'name' => $source->name ]));
    }
}
