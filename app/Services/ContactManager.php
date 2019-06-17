<?php

namespace App\Services;

use App\Models\Database\Address;
use App\Models\Database\Contact;
use App\Models\Database\ExtSource;
use App\Models\Database\User;
use App\Models\Database\UserExt;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\ContactManager as ContactManagerInterface;

class ContactManager implements ContactManagerInterface
{
    protected $contactTypeMap;
    
    public function __construct() {
        $this->contactTypeMap = array_flip(Contact::$contactTypes);    
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::findContact()
     */
    public function findContact(User $user, array $data, $name, $ext_user = null)
    {
        if(!array_key_exists($name, $this->contactTypeMap)) 
            return null;    
        $type = $this->contactTypeMap[$name];
        $class = Contact::$contactModels[$name];
        $obj = new Contact($data);
        
        $query = $user->contacts()->where('type', '=', $type);
        foreach($data as $key => $value) {
            // account for mutators
            $query = $query->where($key, '=', $obj->$key);
        }
        if(!empty($ext_user)) {
            $query = $query->whereHas('userExt', function($query) use ($ext_user) {
                $query->where('user_ext_id', '=', $ext_user->id);
            });
        }
        #$contacts = $query->get()->map(function($contact) use ($class) { return new $class($contact->toArray()); });
        $contacts = $query->withCount('userExt')->get();
        return $contacts;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::findTrustedContacts()
     */
    public function findTrustedContacts(User $user, $type, $trust_level): Collection
    {
        return $user->contacts()->where('type', '=', $type)->where('trust_level', '>=', $trust_level)->get();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::createContact()
     */
    public function createContact(User $user, UserExt $ext_user, array $data, $class = Address::class): Contact
    {
        $contact = new $class;
        $contact->fill($data);
        $contact->createdBy()->associate($ext_user);
        $contact->trust_level = $ext_user->extSource->trust_level;
        $user->contacts()->save($contact);
        $contact->userExt()->attach($ext_user);
        return $contact;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::updateContact()
     */
    public function updateContact(Contact $contact, UserExt $ext_user, array $data): Contact
    {
        $contact->fill($data);
        $contact->updatedBy()->associate($user_ext);
        if($contact->trust_level < $user_ext->extSource->trust_level) {
            $contact->trust_level = $user_ext->extSource->trust_level;
        }
        $contact->save();
        return $contact;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::syncContacts()
     */
    public function syncContacts(User $user, UserExt $ext_user, array $data, $name)
    {
        // current contacts from this UserExt (source)
        $current = $this->findContact($user, array(), $name, $ext_user);

        // data for contact exists:
        //  - no contact yet => add
        //  - same contact exists => attach
        //  - the contact from this source has changed => modify
        foreach($data as $contact_data) {
            // find by data
            $contacts = $this->findContact($user, $contact_data, $name);
            if(empty($contacts) || $contacts->isEmpty()) {
                $contact = $this->createContact($user, $ext_user, $contact_data, Contact::$contactModels[$name]);
            } else {
                // if it is in the current contacts, remove it
                $key = $current->search(function($item, $key) use ($contact_data) {
                    return $item->equalsTo($contact_data);
                });
                if($key !== false) {
                    $current->forget($key);
                } else {
                    // contact exists, but is not yet assigned to the current ext user
                    foreach($contacts as $contact) {
                        if(!$contact->userExt->pluck('id')->contains($ext_user->id)) {
                            $contact->userExt()->attach($ext_user);
                        }
                    }
                }
            }
        }
        
        // $current now contains contacts of the current ext user that were not found in the new data
        // => remove them
        if(!empty($current)) {
            foreach($current as $contact) {
                $contact->userExt()->detach($ext_user);
                if($contact->user_ext_count == 1) {
                    $contact->delete();
                }
            }
        }
    }
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ContactManager::removeContacts()
     */
    public function removeContacts(User $user, ExtSource $ext_source)
    {
        // remove all contacts that have no external source
        return $user->contacts()->doesntHave('userExt')->delete();
    }

    
}

