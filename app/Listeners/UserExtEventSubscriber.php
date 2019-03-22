<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Interfaces\IdentityManager;
use App\Events\UserExtCreatedEvent;
use App\Events\UserExtUpdatedEvent;
use App\Interfaces\UserManager;
use App\Interfaces\UserExtManager;

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
        $this->identity_mgr->buildIdentityForUser($event->user_ext);
    }

    public function onUserUpdated(UserExtUpdatedEvent $event)
    {
        if(empty($event->user->user_id)) {
            // does not have assigned identity 
            $this->identity_mgr->buildIdentityForUser($event->user_ext);
        } else {
            // has identity, find and update user
            $this->user_mgr->updateUserWithContacts($event->user_ext->user, 
                                                    $event->user_ext, 
                                                    $this->user_ext_mgr->getUserResource($event->user_ext)->toArray(null));
        }
    }
    
    public function failed($event, $exception)
    {
        Log::error('Failed to handle ext user event', [ 'event' => $event, 'exception' => $exception ]);
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserExtCreatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserCreated');
        $events->listen('App\Events\UserExtUpdatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserUpdated');
    }
    
}

