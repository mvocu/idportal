<?php

namespace App\Services;

use App\Models\Database\Address;
use App\Models\Database\Contact;
use App\Models\Database\User;
use App\Models\Database\UserExt;
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
    public function findContact(User $user, array $data, $name): Contact
    {
        if(!array_key_exists($name, $this->contactTypeMap)) 
            return null;    
        $type = $this->contactTypeMap[$name];
        $class = Contact::$contactModels[$name];
        
        $query = $user->contacts()->where('type', '=', $type);
        foreach($data as $name => $value) {
            $query = $query->where($name, '=', $value);
        }
        $contacts = $query->get()->map(function($contact) use ($class) { return new $class($contact->toArray()); });
        return $contacts;
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
        $contact->save();
        return $contact;
    }


    
}

