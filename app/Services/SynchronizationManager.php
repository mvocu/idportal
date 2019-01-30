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
        if($connector->supportsUserListing($es)) {
            $users = $connector->listUsers($es);
            return $this->user_ext_mgr->syncUsers($es, $users);
        }
        return null;
    }
    
}

