<?php
namespace App\Interfaces;

use App\Models\Database\User;

interface VotingCodeManager
{
    public function hasActiveVotingCode(User $user);
    
    public function getActiveVotingCode(User $user);
    
    public function assignVotingCode(User $user);
    
    public function hasActiveVotingCodeById($identifier, $id_type);
    
    public function getActiveVotingCodeById($identifier, $id_type);
    
    public function assignVotingCodeById($identifier, $id_type);
    
}

