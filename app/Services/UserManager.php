<?php

namespace App\Services;

use App\Models\Database\User;
use App\Interfaces\UserManager as UserManagerInterface;
use App\Interfaces\ContactManager as ContactManagerInterface;
use App\Models\Database\Contact;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Ramsey\Uuid\Uuid;
use App\Models\Database\Address;
use App\Models\Database\Databox;
use App\Models\Database\ExtSource;
use App\Models\Database\Phone;
use App\Models\Database\Email;
use App\Models\Database\UserExt;
use App\Events\UserCreatedEvent;
use App\Events\UserUpdatedEvent;

class UserManager implements UserManagerInterface
{
    protected $contact_mgr;

    protected $updateRequirements = [
        'phones' => 'sometimes|required|array',
        //'phones.*.phone' => 'required|phone|unique:contact,phone',
        'emails' => 'sometimes|required|array',
        //'emails.*.email' => 'required|email|unique:contact,email',
        'residency' => 'sometimes|required|array',
        'residency.street' => 'required_with:residency|string',
        'residency.city' => 'required_with:residency|string',
        'residency.state' => 'sometimes|required|string',
        //'residency.org_number' => 'required_with:residency|required_without:residency.ev_number|integer',
        //'residency.ev_number' => 'required_with:residency|required_without:residency.org_number|string',
        'address' => 'sometimes|required|array',
        'address.street' => 'required_with:address|string',
        'address.city' => 'required_with:address|string',
        'address.state' => 'sometimes|required|string',
        //'address.org_number' => 'required_with:address|required_without:address.ev_number|integer',
        //'address.ev_number' => 'required_with:address|required_without:address.org_number|string',
        'addressTmp' => 'sometimes|required|array',
        'addressTmp.street' => 'required_with:addressTmp|string',
        'addressTmp.city' => 'required_with:addressTmp|string',
        //'addressTmp.state' => 'sometimes|required|string',
        //'addressTmp.org_number' => 'required_with:addressTmp|required_without:address.ev_number|integer',
        //'addressTmp.ev_number' => 'required_with:addressTmp|required_without:address.org_number|string',
        'addresses' => 'sometimes|required|array',
        'addresses.*.street' => 'required|string',
        'addresses.*.city' => 'required|string',
        'addresses.*.state' => 'sometimes|required|string',
        'addresses.*.org_number' => 'required_without:addresses.*.ev_number|integer',
        'addresses.*.ev_number' => 'required_without:addresses.*.org_number|string',
        //'dataBox' => 'sometimes|required|unique:contact,databox',
        'bankAccounts' => 'sometimes|required|array',
        'bankAccounts.*.bank_account' => 'required|string',
        //'birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'unique:user,birth_code' ],
        'birth_date' => 'sometimes|required|date',
    ];
    
    private $validator;

    public function __construct(ContactManagerInterface $contact_mgr) 
    {
        $this->contact_mgr = $contact_mgr;
    }
    
    public function createUserWithContacts(UserExt $user_ext, array $data, $parent): User 
    {
        if(!$this->validateCreate($data)) {
            $data = $this->getValidData();
        }
        
        $user = new User();
        $user->fill($data);
        $user->createdBy()->associate($user_ext);
        $user->identifier = Uuid::uuid4();
        $user->trust_level = $user_ext->trust_level;
        if(!empty($parent)) {
            $user->parent()->associate($parent);
        }
        
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

        event(new UserCreatedEvent($user->id));
        
        return $user;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::updateUserWithContacts()
     */
    public function updateUserWithContacts(User $user, UserExt $user_ext, array $data, $parent): User
    {
        if(!$this->validateUpdate($user, $data)) {
            $data = $this->getValidData();
        }

        if(isset($user->confidence_level)) {
            unset($user->confidence_level);
        }
        
        $user->updatedBy()->associate($user_ext);
        $user->fill($data);

        if($user_ext->trust_level > $user->trust_level) {
           $user->trust_level = $user_ext->trust_level;
        }

        if(!empty($parent)) {
            $user->parent()->associate($parent);
        } else {
            $user->parent_id = null;
        }
        
        DB::transaction(function() use ($user, $user_ext, $data) {
            
            $user->save();
        
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
                        $user->save(); // neccessary here to correctly sync the contacts later on
                    } else {
                        // update current value
                        $this->contact_mgr->updateContact($contact, $user_ext, $data[$name]);
                    }
                }
            }
        
            // update contacts for contact types
            foreach(Contact::$contactModels as $name => $class) {
                if(array_key_exists($name, $data) && is_array($data[$name])) {
                    $this->contact_mgr->syncContacts($user, $user_ext, $data[$name], $name);
                } else {
                    $this->contact_mgr->syncContacts($user, $user_ext, array(), $name);
                }
            }

            $user->save();
        });
        
        event(new UserUpdatedEvent($user->id));
        
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
     * @see \App\Interfaces\UserManager::removeAccount()
     */
    public function removeAccount(User $user, ExtSource $source)
    {
        $this->contact_mgr->removeContacts($user, $source);
        $trust_level = $user->accounts()
            ->where('ext_source_id', '!=', $source->id)
            ->max('trust_level');
        if(empty($trust_level)) {
            $trust_level = 0;            
        }
        # after removing external account, the resulting user trust level can not increase
        if($user->trust_level > $trust_level) {
            # we are removing account from source with higher trust than the current;
            $user->trust_level = $trust_level; 
            $user->save();
        }
        event(new UserUpdatedEvent($user->id));
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::findUser()
     */
    public function findUser(array $data): Collection
    {
        $results = array();

        /*
         * maybe we have already identified user
         */
        if(array_key_exists('identifier', $data) && !empty($data['identifier'])) {
            $query = User::where('identifier', '=', $data['identifier']);
            $users = $query->get();
            if($users->isNotEmpty()) {
                // there could be only one result
                $user = $users->first();
                $user->confidence_level = 100;
                $result[$user->id] = $user;
                return new Collection($result);
            } else {
                return new Collection();
            }
        }
        
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

    public function validateCreate($user_ext_data) : bool {
        $requirements = $this->updateRequirements;
        //'phones.*.phone' => 'required|phone|unique:contact,phone',
        $requirements['phones.*.phone'] = [
            'required',
            'phone',
            Rule::unique('contact', 'phone')
        ];
        //'emails.*.email' => 'required|email|unique:contact,email',
        $requirements['emails.*.email'] = [
            'required',
            'email',
            Rule::unique('contact', 'email')
        ];
        //'dataBox' => 'sometimes|required|unique:contact,databox',
        $requirements['dataBox'] = [
            'sometimes',
            'required',
            Rule::unique('contact', 'databox')
        ];
        //'birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'unique:user,birth_code' ],
        $requirements['birth_code'] =  [
            'sometimes',
            'required',
            'regex:/\d{9,10}',
            Rule::unique('user', 'birth_code')
        ];
        if(array_key_exists('residency', $user_ext_data)) {
            $requirements['residency.org_number'] = [
                'required_without:residency.ev_number',
                'integer'
            ];
            $requirements['residency.ev_number'] = [
                'required_without:residency.org_number',
                'string'
            ];
        }
        if(array_key_exists('address', $user_ext_data)) {
            $requirements['address.org_number'] = [
                'required_without:address.ev_number',
                'integer'
            ];
            $requirements['address.ev_number'] = [
                'required_without:address.org_number',
                'string'
            ];
        }
        if(array_key_exists('addressTmp', $user_ext_data)) {
            $requirements['addressTmp.org_number']  = [
                'required_without:addressTmp.ev_number',
                'integer'
            ];
            $requirements['addressTmp.ev_number'] = [
                'required_without:addressTmp.org_number',
                'string'
            ];
        }
        $this->validator = Validator::make($user_ext_data, $requirements);
        return $this->validator->passes();
    }

    public function validateUpdate(User $user, $user_ext_data) : bool {
        $requirements = $this->updateRequirements;
        //'phones.*.phone' => 'required|phone|unique:contact,phone',
        $requirements['phones.*.phone'] = [
            'required',
            'phone',
            Rule::unique('contact', 'phone')->ignore($user->id, 'user_id')
        ];
        //'emails.*.email' => 'required|email|unique:contact,email',
        $requirements['emails.*.email'] = [
            'required',
            'email',
            Rule::unique('contact', 'email')->ignore($user->id, 'user_id')
        ];
        //'dataBox' => 'sometimes|required|unique:contact,databox',
        $requirements['dataBox'] = [
            'sometimes',
            'required',
            Rule::unique('contact', 'databox')->ignore($user->id, 'user_id')
        ];
        //'birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'unique:user,birth_code' ],
        $requirements['birth_code'] =  [
            'sometimes',
            'required',
            'regex:/\d{9,10}',
            Rule::unique('user', 'birth_code')->ignore($user->id, 'user_id')
        ];
        if(array_key_exists('residency', $user_ext_data)) {
            $requirements['residency.org_number'] = [
                'required_without:residency.ev_number',
                'integer'
                ];
            $requirements['residency.ev_number'] = [
                'required_without:residency.org_number',
                'string'
                ];
        }
        if(array_key_exists('address', $user_ext_data)) {
            $requirements['address.org_number'] = [
                'required_without:address.ev_number',
                'integer'
                ];
            $requirements['address.ev_number'] = [
                'required_without:address.org_number',
                'string'
                ];
        }
        if(array_key_exists('addressTmp', $user_ext_data)) {
            $requirements['addressTmp.org_number']  = [ 
                'required_without:addressTmp.ev_number',
                'integer'
                ];
            $requirements['addressTmp.ev_number'] = [
                'required_without:addressTmp.org_number',
                'string'
                ];
        }
        $this->validator = Validator::make($user_ext_data, $requirements);
        return $this->validator->passes();
    }
    
    public function getValidData() 
    {
        return $this->validator->valid();
    }
    
    public function getValidationErrors()
    {
        return $this->validator->errors();    
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

