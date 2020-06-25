<?php

namespace App\Services;

use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\User;
use App\Models\Database\UserExt;
use Illuminate\Support\Facades\Validator;
use App\Interfaces\ContactManager;
use App\Models\Database\Contact;
use App\Events\UserIdentityFailedEvent;
use App\Events\UserUpdatedEvent;
use Illuminate\Support\MessageBag;

class IdentityManager implements IdentityManagerInterface
{
    protected $identityRequirements = [
        'first_name' => 'sometimes|required|string',
        'last_name' => 'sometimes|required|string',
        'phones' => 'required_without:emails|array',
        'phones.*.phone' => 'required|phone|unique:contact,phone',
        'emails' => 'required_without:phones|array',
        'emails.*.email' => 'required|email|unique:contact,email',
        'residency' => 'sometimes|required|array',
        'residency.street' => 'required_with:residency|string',
        'residency.city' => 'required_with:residency|string',
        'residency.state' => 'sometimes|required|string',
        // 'residency.org_number' => 'required_with:residency|required_without:residency.ev_number|integer',
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
        'addressTmp.state' => 'sometimes|required|string',
        //'addressTmp.org_number' => 'required_with:addressTmp|required_without:address.ev_number|integer',
        //'addressTmp.ev_number' => 'required_with:addressTmp|required_without:address.org_number|string',
        'addresses' => 'sometimes|required|array',
        'addresses.*.street' => 'required|string',
        'addresses.*.city' => 'required|string',
        'addresses.*.state' => 'sometimes|required|string',
        'addresses.*.org_number' => 'required_without:addresses.*.ev_number|integer',
        'addresses.*.ev_number' => 'required_without:addresses.*.org_number|string',
        'dataBox' => 'sometimes|required|unique:contact,databox',
        'bankAccounts' => 'sometimes|required|array',
        'bankAccounts.*.bank_account' => 'required|string',
        'birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'unique:user,birth_code' ],
        'birth_date' => 'sometimes|required|date',
    ];
    
    protected $sameIdentityRequirements = [
        'candidate.first_name' => 'sometimes|required|string|similar:user.first_name',
        'candidate.last_name' => 'sometimes|required|string|similar:user.last_name',
        'candidate.birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'same_if_exists:user.birth_code' ],
        'candidate.birth_date' => 'sometimes|required|date|same_date_if_exists:user.birth_date',
        'candidate.dataBox' => 'sometimes|required|string|same_if_exists:user.dataBox',
    ];
    
   
    protected $user_mgr;
    protected $user_ext_mgr;
    protected $ext_source_mgr;
    protected $contact_mgr;
    
    private $validator;
    
    public function __construct(UserManager $user_mgr, 
                                ExtSourceManager $ext_source_mgr, 
                                UserExtManager $user_ext_mgr,
                                ContactManager $contact_mgr) {
        $this->user_mgr = $user_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->ext_source_mgr = $ext_source_mgr;
        $this->contact_mgr = $contact_mgr;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::buildIdentityForUser()
     */
    public function buildIdentityForUser(UserExt $user_ext)
    {
        $user = $user_ext->user;
        if(!empty($user)) {
            return $user;
        }
        
        $user_ext_data = $this->user_ext_mgr->getUserResource($user_ext)->toArray(null);
        $users = $this->user_mgr->findUser($user_ext_data);
        if($users->count() > 1) {
            // more users were found for this single external record
            event(new UserIdentityFailedEvent($user_ext->id, new MessageBag(['failure' => 'More than one candidate found.'])));
            return $users->pluck('id')->toArray();
        }
        
        $user = null;
        $this->validator = null;
        if($users->isEmpty()) {
            $parent = null;
            // check and try to find parent
            if(!empty($user_ext->parent)) {
                $parent_candidates = $this->user_mgr->findUser($user_ext_data['parent']);
                if($parent_candidates->count() == 1) {
                    $parent = $parent_candidates->first();
                }
            }
            // no known identity was found for this record, try to build one 
            if($this->validateIdentity($user_ext_data)) {
                $user = $this->user_mgr->createUserWithContacts($user_ext, $user_ext_data, $parent);
            } else {
                // try to build identity of data that remained after validation
                $data = $this->validator->valid();
                if(!empty($parent) || $this->validateIdentity($data)) {
                    $user = $this->user_mgr->createUserWithContacts($user_ext, $data, $parent);
                }
            }
        } else {
            // we already know identity for this record
            $user = $users->first();
            if($this->user_mgr->validateUpdate($user, $user_ext_data)) {
                $data = $user_ext_data;
            } else {
                $data = $this->user_mgr->getValidData();
            }
            $parent = null;
            // check and try to find parent
            if(!empty($user_ext->parent)) {
                $parent_candidates = $this->user_mgr->findUser($user_ext_data['parent']);
                $parent_candidates = $parent_candidates->filter(function($item, $key) {
                    return $item->id != $user->id;
                });
                if($parent_candidates->count() == 1) {
                    $parent = $parent_candidates->first();
                }
            }
            if($this->validateEqualIdentity($user, $data)) {
                // check the required trust level
                $user_trust = $this->user_mgr->getRequiredTrustLevel($user);
                $candidate_trust = $user_ext->trust_level;
                // if adding the candidate to the user identity would lead to identity with higher trust requirements,
                // we should rather build separate identity and possibly merge later,
                // or do not build identity at all 
                if($candidate_trust > $user_trust ||
                    $candidate_trust == $user_trust && 
                    !empty($data->phones) && 
                    $this->contact_mgr->findTrustedContacts($user, Contact::TYPE_PHONE, $user_trust)->isEmpty()) 
                {

                    if($user->confidence_level > 1) {
                        // we are pretty sure this is the same user
                        $this->user_mgr->updateUserWithContacts($user, $user_ext, $data, $parent);
                    } else {
                        // the currently found identity is less trusted, create a new one 
                        if($this->validateIdentity($user_ext_data)) {
                            // this should never happen - we have found identity using this data, so the uniqueness requirement
                            // should not be fulfilled
                            $user = $this->user_mgr->createUserWithContacts($user_ext, $user_ext_data, $parent);
                        } else {
                            // try to build identity of data that remained after validation
                            $data = $this->validator->valid();
                            if($this->validateIdentity($data)) {
                                $user = $this->user_mgr->createUserWithContacts($user_ext, $data);
                            } else {
                                $user = null;
                            }
                        }
                    }
                } else {
                    // we are adding to the more trustworthy identity:
                    // update user with new data
                    $this->user_mgr->updateUserWithContacts($user, $user_ext, $data);
                }
                
            } else {
                $user = null;
            }
        }
        
        if($user != null) {
            $user_ext->user()->associate($user);
            $user_ext->save();
            event(new UserUpdatedEvent($user->id));
            
        } else {
            $errors = $this->validator->errors();
            $errors->add('failure', 'Identity creation failed.');
            event(new UserIdentityFailedEvent($user_ext->id, $errors));
            return $this->validator;
        }
        
        return $user;
    }


    public function validateIdentity(array $user_ext_data) : bool {
        $requirements = $this->identityRequirements;
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
    
    public function validateEqualIdentity(User $user, $user_ext_data) : bool  {
        if(array_key_exists('identifier', $user_ext_data) && $user->identifier == $user_ext_data['identifier']) {
            return true;            
        }
        $data = [ 'user' => $user->toArray(), 'candidate' => $user_ext_data ];
        $this->validator = Validator::make($data, $this->sameIdentityRequirements);
        return $this->validator->passes();
        
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::mergeUser()
     */
    public function mergeUser(User $source, User $dest)
    {
        // reassign accounts (external users)
        foreach($source->accounts as $user_ext) {
            $user_ext->user()->associate($dest);
            $user_ext->save();
        }
        // merge user records and contacts
        $this->user_mgr->mergeUserWithContacts($source, $dest);
    }

}

