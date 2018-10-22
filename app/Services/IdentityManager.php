<?php

namespace App\Services;

use App\Interfaces\IdentityManager as IdentityManagerInterface;
use App\Interfaces\UserExtManager;
use App\Interfaces\UserManager;
use App\Interfaces\ExtSourceManager;
use App\Models\Database\User;
use Exception;

class IdentityManager implements IdentityManagerInterface
{
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
    public function buildIdentityForUser(\App\Models\Database\UserExt $user_ext): User
    {
        $user_ext_data = $this->user_ext_mgr->extractUserWithAttributes($user_ext);
        $users = $this->user_mgr->findUsers($user_ext_data);
        if($users->count() > 1) {
            // more users were found for this single external record
            throw new Exception("Too many users (" . $users->count() . ") found for candidate");
        }
        
        if($users->isEmpty()) {
            // no known identity was found for this record, try to build one            
        } else {
            // we already know identity for this record
        }
    }

}

