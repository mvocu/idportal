<?php
namespace App\Services;

use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Models\User;
use App\Models\Cas\MfaPolicy;

class MfaManager implements MfaManagerInterface
{

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getPolicy()
     */
    public function getPolicy(User$user)
    {
        return new MfaPolicy($user->mfaPolicy);
    }

    
}

