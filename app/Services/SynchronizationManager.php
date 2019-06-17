<?php

namespace App\Services;

use App\Interfaces\SynchronizationManager as SynchronizationManagerInterface;
use App\Models\Database\ExtSource;
use App\Interfaces\ExtSourceManager;
use App\Interfaces\UserExtManager;

class SynchronizationManager implements SynchronizationManagerInterface
{

    protected $ext_source_mgr;
    protected $user_ext_mgr;
    
    
    public function __construct(ExtSourceManager $ext_source_mgr,
                                UserExtManager $user_ext_mgr) 
    {
        $this->ext_source_mgr = $ext_source_mgr;
        $this->user_ext_mgr = $user_ext_mgr;
    }
    
    public function synchronizeExtSource(ExtSource $es)
    {
        $connector = $this->ext_source_mgr->getConnector($es);
        if($connector != null && $connector->supportsUserListing($es)) {
            $users = $connector->listUsers($es);
            if($users != null) {
                // safety check - if the total number of users is too much different, do not delete anything
                if(abs($users->count() - $es->users()->count()) < 5) {
                    return $this->user_ext_mgr->syncUsers($es, $users, true);
                } else {
                    return $this->user_ext_mgr->syncUsers($es, $users, false);
                }
            }
        }
        return null;
    }
    
}

