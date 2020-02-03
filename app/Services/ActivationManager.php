<?php

namespace App\Services;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Interfaces\ActivationManager as ActivationManagerInterface;
use App\Interfaces\UserExtManager;
use App\Models\Database\UserExt;
use App\Models\Database\ExtSource;
use App\Http\Resources\ExtUserResource;

class ActivationManager implements ActivationManagerInterface
{
    protected $tokens;
    protected $user_ext_mgr;
    
    public function __construct(
        TokenRepositoryInterface $tokens,
        UserExtManager $user_ext_mgr
        )
    {
        $this->tokens = $tokens;
        $this->user_ext_mgr = $user_ext_mgr;
    }

    public function sendActivationLink(CanResetPassword $user)
    {
        $user->sendPasswordResetNotification($this->tokens->create($user));
    }
    
    public function validateToken(CanResetPassword $user, $data)
    {
        Validator::make($data, [
            'token' => [
                'required',
                'string',
                function($attribute, $value, $fail) use ($user) {
                    if(!$this->tokens->exists($user, $value)) {
                        $fail($attribute.' is invalid.');
                    }
                }
                ]
            ])->validate();
        $this->tokens->delete($user);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ActivationManager::activateAccount()
     */
    public function activateAccount(CanResetPassword $user): ?UserExt
    {
        $source = ExtSource::where('type', 'Internal')->get()->first();
        if($source == null) throw new ModelNotFoundException();
        $resource = new ExtUserResource([ 'id' => $user->getEmailForPasswordReset(), 'active' => false, 'attributes' => null ]);
        // this triggers async process of identity build
        $user_ext = $this->user_ext_mgr->activateUserByData($source, $resource);
        return $user_ext; 
    }

    
}

