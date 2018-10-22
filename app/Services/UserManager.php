<?php

namespace App\Services;

use App\Models\Database\User;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Models\Database\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use App\Models\Database\Address;
use App\Models\Database\Databox;
use App\Models\Database\Phone;
use App\Models\Database\Email;

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
                        $contact->fill($contact_data);
                        $user->contacts()->save($contact);
                    }
                }
            }
            
            $user->save();
        
        }); // END transaction

        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::findUser()
     */
    public function findUser(array $data): Collection
    {
        $results = array();
        
        if(array_key_exists('phones', $data) && is_array($data['phones'])) {
            $query = Phone::with('user');
            $this->_collectResults($query, $data['phones'], 'phone', $results);
        }
        
        if(array_key_exists('emails', $data) && is_array($data['emails'])) {
            $query = Email::with('user');
            $this->_collectResults($query, $data['emails'], 'email', $results);
        }

        if(array_key_exists('dataBox', $data) && is_array($data['dataBox'])) {
            $query = Databox::with('user');
            $this->_collectResults($query, $data['dataBox'], 'databox', $results);
        }
        
        if(array_key_exists('bankAccounts', $data) && is_array($data['emails'])) {
            $query = Email::with('user');
            $this->_collectResults($query, $data['emails'], 'email', $results);
        }
        
        return new Collection($result);
    }

    protected function _collectResults($query, $data, $field, &$results) {
        $first = array_shift($data);
        $query = $query->where($field, '=', $first);
        foreach($data as $value) {
            $query = $query->orWhere($field, '=', $value);
        }
        $contacts = $query->get();
        foreach($contacts as $contact) {
            $results[$contact->user->id] = $contact->user;
        }
    }
    
}

