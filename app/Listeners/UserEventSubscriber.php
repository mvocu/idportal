<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use App\Interfaces\LdapConnector;
use App\Models\Database\User;

class UserEventSubscriber implements ShouldQueue
{
    protected $ldapc;
    
    public function __construct(LdapConnector $ldapc)
    {
        $this->ldapc = $ldapc;
    }
    
    public function onUserCreated($event)
    {
        $user = User::findOrFail($event->user_id);
        if($user->export_to_ldap) {
            $this->ldapc->createUser($user);
        }
        return true;
    }
    
    public function onUserUpdated($event)
    {
        $user = User::findOrFail($event->user_id);
        if($user->export_to_ldap) {
            $this->ldapc->syncUsers(collect([$user]));
        }
        return true;
    }
    
    public function failed($event, $exception) 
    {
        Log::error('Failed to handle user event', [ 'event' => $event, 'exception' => $exception ]);    
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserUpdatedEvent', 'App\Listeners\UserEventSubscriber@onUserUpdated');
        $events->listen('App\Events\UserCreatedEvent', 'App\Listeners\UserEventSubscriber@onUserCreated');
    }
}

