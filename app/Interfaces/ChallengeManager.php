<?php
namespace App\Interfaces;

interface ChallengeManager
{
    const PHONE_CHALLENGE_KEY = "challenge.phone_number";
    const EMAIL_CHALLENGE_KEY = "challenge.email";
    
    public function createToken($key, ChallengeStore $store);
    
    public function verifyToken($key, ChallengeStore $store, $token);
        
}

