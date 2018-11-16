<?php

namespace App\Services;

use App\Models\Database\Contact;
use App\Models\Database\User;
use App\Interfaces\ConsentManager as ConsentManagerInterface;
use App\Models\Database\ExtSourceAttribute;
use App\Models\Database\UserExtAttribute;

class ConsentManager implements ConsentManagerInterface
{
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

