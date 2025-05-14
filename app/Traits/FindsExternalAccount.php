<?php

namespace App\Traits;

use App\Models\Database\ExtSource;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Http\Resources\UserResource;
use App\Http\Resources\ExtUserResource;

trait FindsExternalAccount {

    protected $ldap_mgr;

    public function findExternalAccount(Authenticatable $user, $client)
    {
        $idp_s = $this->getExtSource($client);
        $auth_user = $this->ldap_mgr->findUserByExtSource($idp_s, $user->getAuthIdentifier());
        return $auth_user;   
    }

    public function findUserByExtIdentity(Authenticatable $user, $client, &$data = null) 
    {
        $user_r = $user->getResource($client);
        $idp_s = $this->getExtSource($client);

        #$data = $this->user_ext_mgr->mapUserAttributes($idp_s, $data);
        #$user_r = new UserResource($data);
        #$data = $user_r->toArray(null);
        #$users = $this->user_mgr->findUser($data);

        #return $users;
        
        $users = $this->findUserByExtResource($idp_s, $user_r, $data);

        return $users;
    }
    
    public function findUserByExtResource(ExtSource $source, ExtUserResource $user_r, &$data = null)
    {
        #$source = ExtSource::where('type', 'Internal')->get()->first();
        $data = $this->user_ext_mgr->mapUserAttributes($source, $user_r);
        $user_r = new UserResource($data);
        $data = $user_r->toArray(null);
        $users = $this->user_mgr->findUser($data);
        
        return $users;
    }
    
    protected function getExtSource($client)
    {
        return ExtSource::where([
            ['name', '=', $client],
            ['identity_provider', '=', 1]
        ])->get()->first();
    }
    
}

