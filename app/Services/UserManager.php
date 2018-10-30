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
    public function findUsers(array $data): Collection
    {
        $results = array();
        
        /*
         * These properties are unique for the identity, even though the identity may posses more instances
         * of them.
         */
        if(array_key_exists('phones', $data) && is_array($data['phones'])) {
            $query = Phone::with('user');
            $this->_normalizePhones($data['phones']);
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
        
        if(array_key_exists('bankAccounts', $data) && is_array($data['bankAccounts'])) {
            $query = Email::with('user');
            $this->_collectResults($query, $data['bankAccounts'], 'bank_account', $results);
        }
        
        if(array_key_exists('birth_code', $data) && !empty($data['birth_code']) && 
            array_key_exists('birth_date', $data) && !empty($data['birth_date']) ) {
            $query = User::where('birth_code', '=', $data['birth_code'])->andWhere('birth_date', '=', $data['birth_date']);
            $users = $query->get();
            foreach($users as $user) {
                if(array_key_exists($user->id, $results)) {
                    $results[$user->id]->confidence_level += 1;
                } else { 
                    $results[$user->id] = $user;
                    $results[$user->id]->confidence_level = 1;
                }
            }
        }
        

        return new Collection($results);
    }

    protected function _normalizePhones(&$data) {
        foreach($data as &$phone) {
            $value = $phone['phone'];
            if(empty($value)) continue;
            $length = strlen($value);
            if($length == 9) {
                $value = "+420" . $value;
            } else {
                if($value[0] != '+')
                    $value = "+" . $value;
            }
            $phone['phone'] = $value;
        }
    }
    
    protected function _collectResults($query, $data, $field, &$results) {
        if(empty($data)) 
            return; // nothing to do
        $run = false;
        foreach($data as $value) {
            if(!empty($value[$field])) {
                if($run) {
                    $query = $query->orWhere($field, '=', $value[$field]);
                } else {
                    $query = $query->where($field, '=', $value[$field]);
                }
                $run = true;
            }
        }
        if(!$run) 
            return;
        $contacts = $query->get();
        foreach($contacts as $contact) {
            if(array_key_exists($contact->user->id, $results)) {
                $results[$contact->user->id]->confidence_level += 1;   
            } else {
                $results[$contact->user->id] = $contact->user;
                $results[$contact->user->id]->confidence_level = 1;
            }
        }
    }
    
}

