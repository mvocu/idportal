<?php
namespace App\Services;

use App\Models\Database\User;
use App\Interfaces\VotingCodeManager as VotingCodeManagerInterface;
use App\Models\Database\VotingCode;
use Illuminate\Database\Eloquent\Builder;

class VotingCodeManager implements VotingCodeManagerInterface
{
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::hasActiveVotingCode()
     */
    public function hasActiveVotingCode(User $user)
    {
        $codes = $user->votingCodes()->where('valid', 1)->get();
        return $codes && !$codes->isEmpty();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::getActiveVotingVode()
     */
    public function getActiveVotingCode(User $user)
    {
        return $user->votingCodes->first();        
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::assignVotingCode()
     */
    public function assignVotingCode(User $user)
    {
        $code = VotingCode::doesntHave('user')->get()->first();
        if($code != null) {
            $code->user()->associate($user);
            $code->save();
            $user->refresh();
            $code->refresh();
            return true;
        }
        return false;
    }
    
}

