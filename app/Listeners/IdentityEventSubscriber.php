<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\IdentityDuplicate;
use App\Models\Database\User;
use App\Models\Database\UserExt;

class IdentityEventSubscriber implements ShouldQueue
{
    public function onIdentityFailed($event)
    {
        Log::error("Failed to create identity: ", [ 'errors' => $event->errors ]);
        return true;
    }

    public function onIdentityDuplicate($event)
    {
        Mail::to(config('mail.support', ""))->send(new IdentityDuplicate(
            UserExt::find($event->user_id), 
            User::find($event->duplicate1_id),
            User::find($event->duplicate2_id),
            $event->errors));
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

