<?php

namespace App\Traits;

use App\Models\Database\ExtSource;
use Illuminate\Contracts\Auth\Authenticatable;

trait FindsExternalAccount {

    protected $ldap_mgr;

    public function findExternalAccount(Authenticatable $user, $client)
    {
        $idp_s = ExtSource::where([
            ['name', '=', $client],
            ['identity_provider', '=', 1]
        ])->get()->first();
        $auth_user = $this->ldap_mgr->findUserByExtSource($idp_s, $user->getAuthIdentifier());
        return $auth_user;   
    }
    
}

