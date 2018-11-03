<?php

namespace App\Services;

use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\User;
use App\Utils\Names;
use Illuminate\Support\Facades\Validator;
use Exception;

class IdentityManager implements IdentityManagerInterface
{
    protected $identityRequirements = [
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'phones' => 'required|array',
        'phones.*.phone' => [ 'required', 'regex:/^[+]?\d[\d\s]*\d$/', 'unique:contact,phone' ],
        'emails' => 'required_without_all:addresses,dataBox,bankAccounts|array',
        'emails.*.email' => 'required|email|unique:contact,email',
        'addresses' => 'sometimes|required|array',
        'addresses.*.street' => 'required|string',
        'addresses.*.city' => 'required|string',
        'addresses.*.state' => 'sometimes|required|string',
        'addresses.*.org_number' => 'required_without:ev_number|integer',
        'addresses.*.ev_number' => 'required_without:org_number|string',
        'dataBox' => 'sometimes|required|unique:contact,databox',
        'bankAccounts' => 'sometimes|required|array',
        'bankAccounts.*.bank_account' => 'required|string',
    ];
    
    protected $user_mgr;
    
    protected $user_ext_mgr;
    
    protected $ext_source_mgr;
    
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
        if($users->isEmpty()) {
            // no known identity was found for this record, try to build one 
            if($this->hasIdentity($user_ext_data)) {
                $user = $this->user_mgr->createUserWithContacts($user_ext_data);
            }
        } else {
            // we already know identity for this record
            $user = $users->first();
            $user = $this->checkIdentity($user, $user_ext_data);
        }
        
        if($user != null) {
            $user_ext->user()->associate($user);
            $user_ext->save();
        }
        
        return $user;
    }


    public function hasIdentity($user_ext_data) : bool {
        $validator = Validator::make($user_ext_data, $this->identityRequirements);
        return $validator->fails() == false;
    }
    
    public function checkIdentity(User $user, $user_ext_data) {
        if($user->trust_level > 1)
            // two or more identifiers 
            return $user;
        
        // only one identifier - check name distance
        $distance = Names::damlev($user->first_name, $user_ext_data['first_name']) +
                    Names::damlev($user->last_name, $user_ext_data['last_name']);
        if($distance < 5)
            return $user;
        
         return null;
    }
}

