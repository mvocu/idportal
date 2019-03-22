<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;

class LoggingSubscriber 
{

    public function handle($event, $data)
    {
        Log::info("Dispatched event " . $event, ['event' => $data]);
        return true;
    }
    
    public function subscribe($events)
    {
        $events->listen('App\Events\*', 'App\Listeners\LoggingSubscriber@handle');
    }
    
}

