<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Interfaces\LdapConnector;

class UserEventSubscriber implements ShouldQueue
{
    protected $ldapc;
    
    public function __construct(LdapConnector $ldapc)
    {
        $this->ldapc = $ldapc;
    }
    
    public function onUserCreated($event)
    {
        $this->ldapc->createUser($event->user);
    }
    
    public function onUserUpdated($event)
    {
        $this->ldapc->syncUsers(collect($event->user));
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserUpdatedEvent', 'App\Listeners\UserEventSubscriber@onUserUpdated');
        $events->listen('App\Events\UserCreatedEvent', 'App\Listeners\UserEventSubscriber@onUserCreated');
    }
}

