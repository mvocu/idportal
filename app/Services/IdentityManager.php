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
        'candidate.birth_date' => 'sometimes|required|date|same_if_exists:user.birth_date',
        'candidate.dataBox' => 'sometimes|required|string|same_if_exists:user.dataBox',
    ];
    
    protected $updateIdentityRequirements = [
        'phones' => 'sometimes|required|array',
        'phones.*.phone' => 'required|phone|unique:contact,phone',
        'emails' => 'sometimes|required|array',
        'emails.*.email' => 'required|email|unique:contact,email',
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
        'addressTmp.ev_number' => 'required_with:addressTmp|required_without:address.org_number|string',
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
            return $users->pluck('id')->toArray();
        }
        
        $user = null;
        $this->validator = null;
        if($users->isEmpty()) {
            // no known identity was found for this record, try to build one 
            if($this->validateIdentity($user_ext_data)) {
                $user = $this->user_mgr->createUserWithContacts($user_ext, $user_ext_data);
            } else {
                // try to build identity of data that remained after validation
                $data = $this->validator->valid();
                if($this->validateIdentity($data)) {
                    $user = $this->user_mgr->createUserWithContacts($user_ext, $data);
                }
            }
        } else {
            // we already know identity for this record
            $user = $users->first();
            if($this->validateUpdate($user_ext_data)) {
                $data = $user_ext_data;
            } else {
                $data = $this->validator->valid();
            }
            if($this->validateEqualIdentity($user, $data)) {
                // check the required trust level
                $user_trust = $this->user_mgr->getRequiredTrustLevel($user);
                $candidate_trust = $user_ext->extSource->trust_level;
                // if adding the candidate to the user identity would lead to identity with higher trust requirements,
                // we should rather build separate identity and possibly merge later 
                if($candidate_trust > $user_trust ||
                    $candidate_trust == $user_trust && 
                    !empty($data->phones) && 
                    $this->contact_mgr->findTrustedContacts($user, Contact::TYPE_PHONE, $user_trust)->isEmpty()) 
                {
                    // the currently found identity is less trusted, create a new one 
                    if($this->validateIdentity($user_ext_data)) {
                        // this should never happen - we have found identity using this data, so the uniqueness requirement
                        // should not be fulfilled
                        $user = $this->user_mgr->createUserWithContacts($user_ext, $user_ext_data);
                    } else {
                        // try to build identity of data that remained after validation
                        $data = $this->validator->valid();
                        if($this->validateIdentity($data)) {
                            $user = $this->user_mgr->createUserWithContacts($user_ext, $data);
                        } else {
                            $user = null;
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
        } else {
            return $this->validator;
        }
        
        return $user;
    }


    public function validateIdentity($user_ext_data) : bool {
        $this->validator = Validator::make($user_ext_data, $this->identityRequirements);
        $this->validator->sometimes('residency.org_number', 'required_without:residency.ev_number|integer',
            function($input) { return !empty($input->residency); }
            );
        $this->validator->sometimes('residency.ev_number', 'required_without:residency.org_number|string',
            function($input) { return !empty($input->residency); }
            );
        $this->validator->sometimes('address.org_number', 'required_without:address.ev_number|integer',
            function($input) { return !empty($input->address); }
            );
        $this->validator->sometimes('address.ev_number', 'required_without:address.org_number|string',
            function($input) { return !empty($input->address); }
            );
        $this->validator->sometimes('addressTmp.org_number', 'required_without:addressTmp.ev_number|integer',
            function($input) { return !empty($input->addressTmp); }
            );
        $this->validator->sometimes('addressTmp.ev_number', 'required_without:addressTmp.org_number|string',
            function($input) { return !empty($input->addressTmp); }
            );
        return $this->validator->passes();
    }
    
    public function validateEqualIdentity(User $user, $user_ext_data) : bool  {
        $data = [ 'user' => $user->toArray(), 'candidate' => $user_ext_data ];
        $this->validator = Validator::make($data, $this->sameIdentityRequirements);
        return $this->validator->passes();
        
    }
    
    public function validateUpdate($user_ext_data) : bool {
        $this->validator = Validator::make($user_ext_data, $this->updateIdentityRequirements);
        $this->validator->sometimes('residency.org_number', 'required_without:residency.ev_number|integer',
            function($input) { return !empty($input->residency); }
            );
        $this->validator->sometimes('residency.ev_number', 'required_without:residency.org_number|string',
            function($input) { return !empty($input->residency); }
            );
        $this->validator->sometimes('address.org_number', 'required_without:address.ev_number|integer',
            function($input) { return !empty($input->address); }
            );
        $this->validator->sometimes('address.ev_number', 'required_without:address.org_number|string',
            function($input) { return !empty($input->address); }
            );
        $this->validator->sometimes('addressTmp.org_number', 'required_without:addressTmp.ev_number|integer',
            function($input) { return !empty($input->addressTmp); }
            );
        $this->validator->sometimes('addressTmp.ev_number', 'required_without:addressTmp.org_number|string',
            function($input) { return !empty($input->addressTmp); }
            );
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

