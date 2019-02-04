<?php

namespace App\Services;

use App\Models\Database\User;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\ContactManager as ContactManagerInterface;
use App\Models\Database\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use App\Models\Database\Address;
use App\Models\Database\Databox;
use App\Models\Database\Phone;
use App\Models\Database\Email;
use App\Models\Database\UserExt;
use App\Http\Resources\UserResource;
use App\Events\UserCreatedEvent;
use App\Events\UserUpdatedEvent;

class UserManager implements UserManagerInterface
{
    protected $contact_mgr;
    
    public function __construct(ContactManagerInterface $contact_mgr) 
    {
        $this->contact_mgr = $contact_mgr;
    }
    
    public function createUserWithContacts(UserExt $user_ext, array $data): User 
    {
        $user = new User();
        $user->fill($data);
        $user->createdBy()->associate($user_ext);
        $user->identifier = Uuid::uuid4();

        DB::transaction(function() use ($user, $user_ext, $data) {

            $user->save();
            
            // save contacts for explicit relations
            foreach(['birth_place', 'residency', 'address', 'address_tmp'] as $name) {
                if(array_key_exists($name, $data) && is_array($data[$name])) {
                    $relationName = Str::camel($name);
                    // XXX - should fetch the model from relation, but...
                    $contact = $this->contact_mgr->createContact($user, $user_ext, $data[$name], Address::class);
                    call_user_func([$user, $relationName])->associate($contact);
                }
            }
            
            // save contacts for contact types
            foreach(Contact::$contactModels as $name => $class) {
                if(array_key_exists($name, $data) && is_array($data[$name])) {
                    foreach($data[$name] as $contact_data) {
                        $contact = $this->contact_mgr->createContact($user, $user_ext, $contact_data, $class);
                    }
                }
            }
            
            $user->save();
        
        }); // END transaction

        event(new UserCreatedEvent($user));
        
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::updateUserWithContacts()
     */
    public function updateUserWithContacts(User $user, UserExt $user_ext, array $data): User
    {
        $user->updatedBy()->associate($user_ext);
        $user->fill($data);
        
        // update contacts for explicit relations
        foreach(['birth_place', 'residency', 'address', 'address_tmp'] as $name) {
            if(array_key_exists($name, $data) && is_array($data[$name])) {
                $relationName = Str::camel($name);
                $contact = call_user_func([$user,$relationName])->first();
                if(empty($contact)) {
                    // no value yet
                    // XXX - should fetch the model from relation, but...
                    $contact = $this->contact_mgr->createContact($user, $user_ext, $data[$name], Address::class);
                    call_user_func([$user, $relationName])->associate($contact);
                } else {
                    // update current value
                    $this->contact_mgr->updateContact($contact, $user_ext, $data[$name]);
                }
            }
        }
        
        // update contacts for contact types
        foreach(Contact::$contactModels as $name => $class) {
            if(array_key_exists($name, $data) && is_array($data[$name])) {
                foreach($data[$name] as $contact_data) {
                    $contacts = $contact_mgr->findContact($user, $contact_data, $name);
                    if(empty($contacts) || $contacts->isEmpty()) {
                        $contact = $this->contact_mgr->createContact($user, $user_ext, $contact_data, $class);
                    } else {
                        // this is not neccessary - unless contact manager performs more intelligent search
                        // $contact = $this->contact_mgr->updateContact($contact, $user_ext, $contact_data);
                    }
                }
            }
        }
        
        event(new UserUpdatedEvent($user));
        
        return $user;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::mergeUserWithContacts()
     */
    public function mergeUserWithContacts(User $source, User $dest)
    {
        foreach($source->contacts as $contact) {
            $contact->user()->associate($dest);
            $contact->save();
        }
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::findUser()
     */
    public function findUser(array $data): Collection
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
        
        if(array_key_exists('birth_code', $data) && !empty($data['birth_code'])) {
            $query = User::where('birth_code', '=', $data['birth_code']);
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

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::getRequiredTrustLevel()
     */
    public function getRequiredTrustLevel(User $user)
    {
        $trust_level = $user->accounts()
        ->join('ext_sources', 'ext_sources.id', '=', 'user_ext.ext_source_id')
        ->max('ext_sources.trust_level');
        return $trust_level;
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

