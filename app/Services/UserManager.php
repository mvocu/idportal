<?php

namespace App\Services;

use App\Models\Database\User;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Models\Database\Contact;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use App\Models\Database\Address;

class UserManager implements UserManagerInterface
{
    
    public function createUserWithContacts(array $data): User {

        $user = new User();

        $user->fill($data);

        $user->identifier = Uuid::uuid4();

        DB::transaction(function() use ($user, $data) {

            $user->save();
            
            if (array_key_exists('birth_place', $data) && is_array($data['birth_place'])) {
                $birthplace = new Address();
                $birthplace->fill($data['birth_place']);
                $user->contacts()->save($birthplace);
                $user->birthPlace()->associate($birthplace);
            }
            
            if (array_key_exists('residency', $data) && is_array($data['residency'])) {
                $residency = new Address();
                $residency->fill($data['residency']);
                $user->contacts()->save($residency);
                $user->residency()->associate($residency);
            }

            if (array_key_exists('address', $data) && is_array($data['address'])) {
                $address = new Address();
                $address->fill($data['address']);
                $user->contacts()->save($address);
                $user->address()->associate($address);
            }

            if (array_key_exists('address_tmp', $data) && is_array($data['address_tmp'])) {
                $address_tmp = new Address();
                $address_tmp->fill($data['address_tmp']);
                $user->contacts()->save($address_tmp);
                $user->addressTmp()->associate($address_tmp);
            }

            foreach(Contact::$contactModels as $name => $class) {
                if(array_key_exists($name, $data) && is_array($data[$name])) {
                    foreach($data[$name] as $contact_data) {
                        $contact = new $class;
                        $contact->fill($data[$name]);
                        $user->contacts()->save($contact);
                    }
                }
            }
            
            $user->save();
        
        }); // END transaction

        return $user;
    }
    
}

