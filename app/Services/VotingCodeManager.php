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
        $code = VotingCode::doesntHave('user', function(Builder $query) {
                    $query->whereNull('identifier');          
                })->get()->first();
        if($code != null) {
            $code->user()->associate($user);
            $code->save();
            $user->refresh();
            $code->refresh();
            return true;
        }
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::assignVotingCodeById()
     */
    public function assignVotingCodeById($identifier, $id_type)
    {
        $code = VotingCode::doesntHave('user')->whereNull('identifier')->get()->first();

        if($code != null) {
            $code->identifier = $identifier;
            $code->identifier_type = $id_type; 
            $code->save();
            $code->refresh();
            return true;
        }
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::getActiveVotingCodeById()
     */
    public function getActiveVotingCodeById($identifier, $id_type)
    {
        # XXX - should also search for user by contacts?
        $code = VotingCode::where('identifier', $identifier)->where('identifier_type', $id_type)->first();
        return $code;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\VotingCodeManager::hasActiveVotingCodeById()
     */
    public function hasActiveVotingCodeById($identifier, $id_type)
    {
        # XXX - should also search for user by contacts?
        $codes = VotingCode::where('identifier', $identifier)->where('identifier_type', $id_type)->get();
        return $codes && !$codes->isEmpty();
    }

}

