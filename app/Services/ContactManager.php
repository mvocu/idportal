<?php

namespace App\Services;

use App\Models\Database\Address;
use App\Models\Database\Contact;
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
    public function findContact(User $user, array $data, $name, $source = null)
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
        $contacts = $query->get()->map(function($contact) use ($class) { return new $class($contact->toArray()); });
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
        $contact->extSources()->attach($ext_user->extSource);
        $contact->trust_level = $ext_user->extSource->trust_level;
        $user->contacts()->save($contact);
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
     * @see \App\Interfaces\ContactManager::attachSource()
     */
    public function attachSource(Contact $contact, UserExt $ext_user): Contact
    {
        $contact->extSources()->attach($ext_user->extSource);
        $contact->save();
        return $contact;
    }
    
}

