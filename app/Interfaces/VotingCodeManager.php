<?php
namespace App\Interfaces;

use App\Models\Database\User;

interface VotingCodeManager
{
    public function hasActiveVotingCode(User $user);
    
    public function getActiveVotingCode(User $user);
    
    public function assignVotingCode(User $user);
    
}

