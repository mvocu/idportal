<?php

namespace App\Http\Controllers;

use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;
use App\Models\Database\User;
use App\Models\Database\UserExt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Http\Resources\UserResource;
use App\Interfaces\ExtSourceManager;
use App\Mail\AccountRequested;

class UserExtController extends Controller
{
    protected $user_ext_mgr;
    protected $ext_source_mgr;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserExtManager $user_ext_mgr, ExtSourceManager $ext_source_mgr)
    {
        $this->user_ext_mgr = $user_ext_mgr;
        $this->ext_source_mgr = $ext_source_mgr;
        $this->middleware(['auth.eidp:MojeID', 'auth.eidp:eIdentita', 'auth']);
    }
    
    public function showAddUserExtForm(Request $request, User $user, ExtSource $source)
    {
        // $attributes = (new UserResource($user))->getExtAttributes($ext_source->attributes);
        if($user->trust_level < $source->trust_level) {
            return view('infouserext', [
                'user' => $user,
                'ext_source' => $source,
            ]);
        }

        $editable = $this->ext_source_mgr->getUpdatableAttributes($source);
        return view('adduserext', [
            'action' => 'add', 
            'user' => $user, 
            'ext_source' => $source, 
            'editable' => $editable,
            'attributes' => array()
        ]);
    }

    public function showUserExtForm(Request $request, User $user, ExtSource $source)
    {
        //$attributes = (new UserResource($user))->getExtAttributes($ext_source->attributes);
        $user_ext = $user->accounts()->where('ext_source_id', $source->id)->get()->first();
        if(is_null($user_ext)) {
            return back()
            ->withErrors(
                ['failure' => __('User :user at :esource not found', 
                    [ 'user' => $user->id, 'esource' => $source->id])                
            ]);
        }
        $attributes = $user_ext->attributes->mapWithKeys( function($item) {
            return [ $item->attrDesc->display_name => $item->value ];
        });
        $editable = $this->ext_source_mgr->getUpdatableAttributes($source);
        return view('userext', [
            'action' => 'edit',
            'user' => $user,
            'user_ext' => $user_ext,
            'ext_source' => $source,
            'editable' => $editable,
            'attributes' => $attributes
        ]);
    }

    public function modifyUserExt(Request $request, UserExt $user_ext, $action)
    {
        $source = $user_ext->extSource;
        $editable = $this->ext_source_mgr->getUpdatableAttributes($source);
        $editable_names = $editable->pluck('name')->all();
        $source_c = $this->ext_source_mgr->getConnector($source);
        $update_data = $request->only($editable_names);
        $r_user_ext = $this->user_ext_mgr->getExtUserResource($user_ext);
        $validator = null;
        if(!$source_c->validateUpdate($source, $update_data, $validator)) {
            return back()
            ->withInput($update_data)
            ->withErrors($validator);
        }
        try {
            $r_user_ext_new = $source_c->modifyUser($r_user_ext, $update_data);
            $this->user_ext_mgr->updateUserWithAttributes($source, $user_ext, $r_user_ext_new);
        } catch(Exception $e) {
            return back()
            ->withInput($update_data)
            ->withErrors(['failure' => __($e->getMessage())]);
        }
        return back()
        ->with('status', __('Saved.'));
    }

    public function addUserExt(Request $request, User $user, ExtSource $source)
    {
        Mail::to(config('mail.support', ""))->send(new AccountRequested($user, $source));
        return redirect()
            ->route('home')
            ->with(['status' => __('Your request has been sent to our personnel.') ]);
    }
    
    public function removeUserExt(Request $request, UserExt $user_ext)
    {
        $source = $user_ext->extSource;
        $this->user_ext_mgr->removeUser($source, $user_ext);
        return back()
        ->with('status', __('External identity removed'));
    }
}
