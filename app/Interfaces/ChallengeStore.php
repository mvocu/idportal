<?php
namespace App\Interfaces;

interface ChallengeStore
{
    public function saveChallenge($key, $data);
    
    public function getChallenge($key);
    
    public function removeChallenge($key);
}

