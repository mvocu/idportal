<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LoggingSubscriber implements ShouldQueue
{

    public function handle($event)
    {
        Log::info("Dispatched event", ['event' => $event]);
    }
    
    public function subscribe($events)
    {
        $events->listen('*', 'App\Listeners\LoggingSubscriber');
    }
    
}

