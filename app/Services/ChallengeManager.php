<?php
namespace App\Services;

use App\Interfaces\ChallengeManager as ChallengeManagerInterface;
use App\Interfaces\ChallengeStore;
use Illuminate\Support\Str;

class ChallengeManager implements ChallengeManagerInterface
{
    public function createToken($key, ChallengeStore $store) {
        $token = $this->_createToken();
        return $store->saveChallenge($key, $token);         
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ChallengeManager::verifyToken()
     */
    public function verifyToken($key, ChallengeStore $store, $token)
    {
        $saved = $store->getChallenge($key);
        return $saved == $token;
    }
    
    protected function _createToken() {
        $random = Str::random(12);
        $random = strtr($random, "IlO0", "ijQ7");
        return substr($random, 0, 3) . "-" . substr($random,3,3) . "-" . substr($random, 6, 3) . "-" . substr($random, 9, 3);
    }

}

