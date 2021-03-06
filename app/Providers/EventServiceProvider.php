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
        // 'App\Events\UserExtRemovedEvent' => [],
        // 'App\Events\UserUpdatedEvent' => [],
        // 'App\Events\UserCreatedEvent' => [],
        // 'App\Events\LdapUserCreatedEvent' => [],
        // 'App\Events\LdapUserUpdatedEvent' => [],
        // 'App\Events\UserIdentityFailedEvent' => [],
        // 'App\Events\UserIdentityDuplicateEvent' => [],
        // 'Aacotroneo\Saml2\Events\Saml2LoginEvent' => [],
        // 'Aacotroneo\Saml2\Events\Saml2LogoutEvent' => [],
    ];

    protected $subscribe = [
        'App\Listeners\UserExtEventSubscriber',
        'App\Listeners\UserEventSubscriber',
        'App\Listeners\IdentityEventSubscriber',
        'App\Listeners\LoggingSubscriber',
        'App\Listeners\SamlEventSubscriber'
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
