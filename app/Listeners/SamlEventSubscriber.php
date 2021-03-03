<?php

namespace App\Listeners;

use Aacotroneo\Saml2\Events\Saml2LoginEvent;
use Illuminate\Auth\AuthenticationException;
use Aacotroneo\Saml2\Events\Saml2LogoutEvent;

class SamlEventSubscriber
{
    public function __construct()
    {
    }
    
    public function onLoginEvent(Saml2LoginEvent $event)
    {
        $message_id = $event->getSaml2Auth()->getLastMessageId();
        $last_message_id = session('saml2_last_id', null);
        if($message_id == $last_message_id) {
            throw new AuthenticationException("Possible replay attack.");
        }
        
        $user = $event->getSaml2User();
        
        $session = session();
        $session->flash('saml2_last_id', $message_id);
        $session->flash('saml2_nameid', $user->getNameId());
        $session->flash('saml2_assertion', $user->getRawSamlAssertion());
    }
    
    public function onLogoutEvent(Saml2LogoutEvent $event) {
        session(['saml2_last_id' => null, 'saml2_nameid' => null, 'saml2_assertion' => null, 'saml2_logout' => "1"]);
    }
    
    public function subscribe($events)
    {
        $events->listen('Aacotroneo\Saml2\Events\Saml2LoginEvent', 'App\Listeners\SamlEventSubscriber@onLoginEvent');
        $events->listen('Aacotroneo\Saml2\Events\Saml2LogoutEvent', 'App\Listeners\SamlEventSubscriber@onLogoutEvent');
    }
    
}

