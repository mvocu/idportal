<?php

namespace App\Services;

use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\User;
use Illuminate\Support\Facades\Validator;
use Exception;

class IdentityManager implements IdentityManagerInterface
{
    protected $identityRequirements = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'phones' => 'required|array',
        'phones.*.phone' => [ 'required', 'regex:/^[+]?\d[\d\s]*\d$/', 'unique:contact,phone' ],
        'emails' => 'required_without_all:residency,address,addressTmp,addresses,dataBox,bankAccounts|array',
        'emails.*.email' => 'required|email|unique:contact,email',
        'residency' => 'sometimes|required|array',
        'residency.street' => 'required_with:residency|string',
        'residency.city' => 'required_with:residency|string',
        'residency.state' => 'sometimes|required|string',
        'residency.org_number' => 'required_with:residency|required_without:residency.ev_number|integer',
        'residency.ev_number' => 'required_with:residency|required_without:residency.org_number|string',
        'address' => 'sometimes|required|array',
        'address.street' => 'required_with:address|string',
        'address.city' => 'required_with:address|string',
        'address.state' => 'sometimes|required|string',
        'address.org_number' => 'required_with:address|required_without:address.ev_number|integer',
        'address.ev_number' => 'required_with:address|required_without:address.org_number|string',
        'addressTmp' => 'sometimes|required|array',
        'addressTmp.street' => 'required_with:addressTmp|string',
        'addressTmp.city' => 'required_with:addressTmp|string',
        'addressTmp.state' => 'sometimes|required|string',
        'addressTmp.org_number' => 'required_with:addressTmp|required_without:address.ev_number|integer',
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
    
    protected $sameIdentityRequirements = [
        'candidate.first_name' => 'required|string|similar:user.first_name',
        'candidate.last_name' => 'required|string|similar:user.last_name',
        'candidate.birth_code' => [ 'sometimes', 'required', 'regex:/\d{9,10}/', 'same_if_exists:user.birth_code' ],
        'candidate.birth_date' => 'sometimes|required|date|same_if_exists:user.birth_date',
        'candidate.dataBox' => 'sometimes|required|string|same_if_exists:user.dataBox',
    ];
    
    protected $updateIdentityRequirements = [
        'phones' => 'sometimes|required|array',
        'phones.*.phone' => [ 'required', 'regex:/^[+]?\d[\d\s]*\d$/', 'unique:contact,phone' ],
        'emails' => 'sometimes|required|array',
        'emails.*.email' => 'required|email|unique:contact,email',
        'residency' => 'sometimes|required|array',
        'residency.street' => 'required_with:residency|string',
        'residency.city' => 'required_with:residency|string',
        'residency.state' => 'sometimes|required|string',
        'residency.org_number' => 'required_with:residency|required_without:residency.ev_number|integer',
        'residency.ev_number' => 'required_with:residency|required_without:residency.org_number|string',
        'address' => 'sometimes|required|array',
        'address.street' => 'required_with:address|string',
        'address.city' => 'required_with:address|string',
        'address.state' => 'sometimes|required|string',
        'address.org_number' => 'required_with:address|required_without:address.ev_number|integer',
        'address.ev_number' => 'required_with:address|required_without:address.org_number|string',
        'addressTmp' => 'sometimes|required|array',
        'addressTmp.street' => 'required_with:addressTmp|string',
        'addressTmp.city' => 'required_with:addressTmp|string',
        'addressTmp.state' => 'sometimes|required|string',
        'addressTmp.org_number' => 'required_with:addressTmp|required_without:address.ev_number|integer',
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
    
    private $validator;
    
    public function __construct(UserManager $user_mgr, 
                                ExtSourceManager $ext_source_mgr, 
                                UserExtManager $user_ext_mgr) {
        $this->user_mgr = $user_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
        $this->ext_source_mgr = $ext_source_mgr;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\IdentityManager::buildIdentityForUser()
     */
    public function buildIdentityForUser(\App\Models\Database\UserExt $user_ext)
    {
        $user = $user_ext->user;
        if(!empty($user)) {
            return $user;
        }
        
        $user_ext_data = $this->user_ext_mgr->extractUserWithAttributes($user_ext);
        $users = $this->user_mgr->findUser($user_ext_data);
        if($users->count() > 1) {
            // more users were found for this single external record
            throw new Exception("Too many users (" . $users->count() . ") found for candidate");
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
            if($this->validateEqualIdentity($user, $user_ext_data)) {
                // possibly update user with new data
                if($this->validateUpdate($user_ext_data)) {
                    $update = $this->validator->valid();
                    $this->user_mgr->updateUserWithContacts($user, $user_ext, $update);
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
        return $this->validator->passes();
    }
    
    public function validateEqualIdentity(User $user, $user_ext_data) : bool  {
        if($user->trust_level > 1)
            // two or more identifiers 
            return true;
        
        $data = [ 'user' => $user->toArray(), 'candidate' => $user_ext_data ];
        $this->validator = Validator::make($data, $this->sameIdentityRequirements);
        return $this->validator->passes();
        
    }
    
    public function validateUpdate($user_ext_data) : bool {
        $this->validator = Validator::make($user_ext_data, $this->updateIdentityRequirements);
        return $this->validator->passes();
    }
}

