<?php
namespace App\Services;

use App\Interfaces\UserManager as UserManagerInterface;
use App\Models\Ldap\User as LdapUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use App\Interfaces\IdentityResource;

class UserManager implements UserManagerInterface
{

    public function findUserByIdentifier($id)
    {
        $query = LdapUser::query();
        $query
            ->whereRaw('objectclass', '=', '*')
            ->orWhereEquals('cunipersonalid', $id)
            ->orWhereEquals('uid', $id)
            ->orWhereEquals('mail', $id)
            ->orWhereEquals('mailalternateaddress', $id)
            ->orWhereEquals('telephonenumber', $id);
            #->orWhereEquals('pager', $value);
            return $query->get();
    }
    
    public function findUserByData($data)
    {
        $query = LdapUser::query();
        $birthdate = new Carbon($data[IdentityResource::ATTR_BIRTHDATE], 'UTC');
        // required criteria
        $query
            ->where([
                'givenname' => $data[IdentityResource::ATTR_GIVEN_NAME],
                'sn' => $data[IdentityResource::ATTR_FAMILY_NAME],
                'cunibirthdate' => $birthdate->format("YmdHis") . 'Z',
            ]);
            
        // optional criteria (used only when present)
        if(isset($data[IdentityResource::ATTR_EMAIL])) {
            $query->where('mail', $data[IdentityResource::ATTR_EMAIL]);
        }
        return $query->get();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserManager::getIdentity()
     */
    public function getIdentity($user)
    {
        $identityResource = App::makeWith('cas.resource', [ 'resource' => $user ]);
        return $identityResource->toArray(null);
    }

    
}

