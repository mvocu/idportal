<?php
namespace App\Services;

use App\Interfaces\ChallengeManager as ChallengeManagerInterface;
use App\Interfaces\ChallengeStore;

class ChallengeManager implements ChallengeManagerInterface
{
    public function createToken($key, ChallengeStore $store) {
        return $store->saveChallenge($key, "xyz");         
    }
}

