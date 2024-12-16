<?php
namespace App\Interfaces;

interface ChallengeManager
{
    const PHONE_CHALLENGE_KEY = "phone_number";
    
    
    public function createToken($key, ChallengeStore $store);
    
    public function verifyToken($key, ChallengeStore $store, $token);
        
}

