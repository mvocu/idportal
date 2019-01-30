<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class UserExtEventSubscriber implements ShouldQueue
{
    public function onUserCreated($event) 
    {
        
    }

    public function onUserUpdated($event)
    {
        
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserExtCreatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserCreated');
        $events->listen('App\Events\UserExtUpdatedEvent', 'App\Listeners\UserExtEventSubscriber@onUserUpdated');
    }
    
}

