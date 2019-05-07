<?php

namespace App\Interfaces;

use App\Models\Database\User;
use App\Models\Database\Contact;
use App\Models\Database\Address;
use App\Models\Database\UserExt;
use Illuminate\Database\Eloquent\Collection;

interface ContactManager
{
    public function findContact(User $user, array $data, $name, $ext_user = null);
    
    public function createContact(User $user, UserExt $ext_user, array $data, $class = Address::class) : Contact;
    
    public function updateContact(Contact $contact, UserExt $ext_user, array $data) : Contact;
    
    public function syncContacts(User $user, UserExt $ext_user, array $data, $name);
    
    public function findTrustedContacts(User $user, $type, $trust_level) : Collection; 
}

