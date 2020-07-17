<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class IdentityEventSubscriber implements ShouldQueue
{
    public function onIdentityFailed($event)
    {
        return true;
    }

    public function onIdentityDuplicate($event)
    {
        return true;
    }
    
    public function failed($event, $exception)
    {
        Log::error('Failed to handle identity event', [ 'event' => $event, 'exception' => $exception ]);
    }
    
    public function subscribe($events) 
    {
        $events->listen('App\Events\UserIdentityFailedEvent', 'App\Listeners\IdentityEventSubscriber@onIdentityFailed');
        $events->listen('App\Events\UserIdentityDuplicateEvent', 'App\Listeners\IdentityEventSubscriber@onIdentityDuplicate');
    }
}

