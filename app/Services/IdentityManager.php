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
        'emails' => 'required|array',
        'phones.*.phone' => [ 'required', 'regex:/^[+]?\d+/' ],
        'emails.*.email' => 'required|email',
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
        $users = $this->user_mgr->findUsers($user_ext_data);
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
}

