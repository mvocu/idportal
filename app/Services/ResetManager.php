<?php
namespace App\Services;

use App\Auth\AnonymousUser;
use App\Interfaces\ResetManager as ResetManagerInterface;
use App\Models\Ldap\User as LdapUser;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;

class ResetManager implements ResetManagerInterface
{
    const RESET_METHODS = [
        'NIA' => [],
        'svipeid' => [],
        'eduid' => [],
        'edugain' => [],
        'sms-challenge' => [],
        'mail-challenge' => [],
    ];
    
    public function getAvailableMethods(LdapUser $model = null) 
    {
        if($model == null) {
            return collect(array_keys(self::RESET_METHODS));
        }
        $user = new User(new AnonymousUser(), $model);
        $gate = app(Gate::class)->forUser($user);
        $methods = collect(array_keys(self::RESET_METHODS))
            ->filter(fn($it) => $gate->allows("password-reset-using-" . $it, $this));
        return $methods;;
    }
    
    public function getMethodDescription($method = null)
    {
        if($method == null || !array_key_exists($method, self::RESET_METHODS)) {
            return self::RESET_METHODS;
        }
        return self::RESET_METHODS[$method];
    }
    
}

