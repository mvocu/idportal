<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Interfaces\IdentityManager;
use App\Events\UserExtCreatedEvent;
use App\Events\UserExtUpdatedEvent;
use App\Interfaces\UserManager;
use App\Interfaces\UserExtManager;
use App\Models\Database\ExtSource;
use App\Models\Database\User;
use App\Models\Database\UserExt;
use App\Events\UserExtRemovedEvent;

class UserExtEventSubscriber implements ShouldQueue
{
    protected $identity_mgr;
    protected $user_mgr;
    protected $user_ext_mgr;
    
    public function __construct(IdentityManager $identity_mgr, 
                                UserManager $user_mgr,
                                UserExtManager $user_ext_mgr)
    {
        $this->identity_mgr = $identity_mgr;    
        $this->user_mgr = $user_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
    }
    
    public function onUserCreated(UserExtCreatedEvent $event) 
    {
        $user_ext = UserExt::findOrFail($event->user_ext_id);
        $this->identity_mgr->buildIdentityForUser($user_ext);
        return true;
    }

    public function onUserUpdated(UserExtUpdatedEvent $event)
    {
        $user_ext = UserExt::findOrFail($event->user_ext_id);
        if(empty($user_ext->user_id)) {
            // does not have assigned identity 
            $this->identity_mgr->buildIdentityForUser($user_ext);
        } else {
            // has identity, find and update user
            $data = $this->user_ext_mgr->getUserResource($user_ext)->toArray(null);
            $parent = null;
            // check and try to find parent
            if(!empty($user_ext->parent) and empty($user_ext->user->parent_id)) {
                $parent_candidates = $this->user_mgr->findUser($data['parent']);
                $parent_candidates = $parent_candidates->filter(function($item, $key) use ($user_ext) {
                    return $item->id != $user_ext->user_id;
                });
                if($parent_candidates->count() == 1) {
                        $parent = $parent_candidates->first();
                }
            } else if (!empty($user_ext->user->parent_id)) {
                $parent = $user_ext->user->parent;
            }
            $this->user_mgr->updateUserWithContacts($user_ext->user, 
                                                    $user_ext, 
                                                    $data,
                                                    $parent);
        }
        return true;
    }
    
    public function onUserRemoved(UserExtRemovedEvent $event)
    {
        $user = User::findOrFail($event->user_id);
        $source = ExtSource::findOrFail($event->ext_source_id);
        $this->user_mgr->removeAccount($user, $source);
    }
    
    public function failed($event, $exception)
    {
        Log::error('Failed to handle ext user event', [ 'event' => $event, 'exception' => $exception ]);
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserExtCreatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserCreated');
        $events->listen('App\Events\UserExtUpdatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserUpdated');
        $events->listen('App\Events\UserExtRemovedEvent', 'App\Listeners\UserExtEventSubscriber@onUserRemoved');
    }
    
}

