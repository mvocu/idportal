<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LoggingSubscriber 
{

    public function handle($event)
    {
        Log::info("Dispatched event", ['event' => $event]);
        return 0;
    }
    
    public function subscribe($events)
    {
        $events->listen('App\Events\*', 'App\Listeners\LoggingSubscriber@handle');
    }
    
}

