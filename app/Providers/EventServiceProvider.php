<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 'App\Events\UserExtCreatedEvent' => [],
        // 'App\Events\UserExtUpdatedEvent' => [],
        // 'App\Events\UserUpdatedEvent' => [],
        // 'App\Events\UserCreatedEvent' => [],
        // 'App\Events\LdapUserCreatedEvent' => [],
        // 'App\Events\LdapUserUpdatedEvent' => [],
    ];

    protected $subscribe = [
        'App\Listeners\UserExtEventSubscriber',
        'App\Listeners\UserEventSubscriber',
        'App\Listeners\LoggingSubscriber'
    ];
    
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
