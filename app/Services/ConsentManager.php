<?php

namespace App\Services;

use App\Models\Database\Contact;
use App\Models\Database\User;
use App\Interfaces\ConsentManager as ConsentManagerInterface;
use App\Models\Database\ExtSourceAttribute;
use App\Models\Database\UserExtAttribute;
use App\Events\UserUpdatedEvent;

use Carbon\Carbon;

class ConsentManager implements ConsentManagerInterface
{
    public const CONSENT_VALIDITY = 365;
    public const CONSENT_EXPIRES_SOON = 30;
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::isAllowed()
     */
    public function isAllowed($object, $attr, $value): bool
    {
        // TODO Auto-generated method stub
        if($object instanceof User) {
            return $this->isAllowedUserAttr($object, $attr, $value);
        }
    }

    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::hasActiveConsent()
     */
    public function hasActiveConsent(User $user): bool
    {
        if(is_null($user->consent_at)) {
            return false;
        }
        
        $consent_date = new Carbon($user->consent_at);
        return $consent_date->diffInDays(Carbon::now(), true) < self::CONSENT_VALIDITY;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::hasDeniedConsent()
     */
    public function hasDeniedConsent(User $user): bool
    {
        if($this->hasActiveConsent($user)) {
            return false;
        }
        if(is_null($user->consent_requested)) {
            return false;
        }
        $request_date = new Carbon($user->consent_requested);
        return $request_date->diffInRealMinutes(Carbon::now(), true) > 60;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::setConsent()
     */
    public function setConsent(User $user, $active)
    {
        if($active) {
            $user->consent_at = Carbon::now();
        } else {
            $user->consent_at = null;
        }
        $user->save();
        event(new UserUpdatedEvent($user->id));
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::setConsentRequested()
     */
    public function setConsentRequested(User $user, $active)
    {
        if($active) {
            $user->consent_requested = Carbon::now();
        } else {
            $user->consent_requested = null;
        }
        $user->save();
        event(new UserUpdatedEvent($user->id));
    }

    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::getExpiryDate()
     */
    public function getExpiryDate($user)
    {
        $consent_date = new Carbon($user->consent_at);
        $consent_date->addDays(self::CONSENT_VALIDITY);
        return $consent_date;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ConsentManager::getExpiryDate()
     */
    public function expiresSoon($user) 
    {
        if(!$this->hasActiveConsent($user)) {
            return false;
        }
        $consent_date = $this->getExpiryDate($user);
        return $consent_date->diffInDays(Carbon::now()) < self::CONSENT_EXPIRES_SOON;
    }
    
    protected function isAllowedUserAttr(User $user, $attr, $value) {
        // get all values of these attributes from consented sources for the particular user
        $attrs = UserExtAttribute::whereHas('user', function($query) {
            // user has given consent for this source
            $query->where('consent', '=', 1)->orWhereHas('extSource', function ($query) {
                // ...or the source does not require explicit consent
                $query->where('consent_required', '=', '0');
            });
        })->whereHas('attrDesc', function ($query) {
            // get all attribute definitions that give the required attribute
            $query->where('core_name', '=', $attr);
        })->get();
        
    }
    
    protected function isAllowedContactAttr(Contact $contact, $attr, $value) {
        
    }
    
}

