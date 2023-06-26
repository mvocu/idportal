<?php
namespace App\Services;

use App\Interfaces\UserExtManager as UserExtManagerInterface;
use App\Models\Ldap\User;

class UserExtManager implements UserExtManagerInterface
{

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::findUserByExtIdentity()
     */
    public function findUserByExtIdentity($id)
    {
        return User::findBy('cuniprincipalname', $id);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\UserExtManager::listExternalIdentities()
     */
    public function listExternalIdentities(User $user)
    {
        $attrs = $user->getAttributes();
        $result = [];
        foreach($attrs as $name => $value) {
            if(strpos($name, "cuniprincipalname;x-ext-") !== false) {
                $provider = substr($name, strlen("cuniprincipalname;x-ext-"));
                $result[$provider] = is_array($value) ? $value[0] : $value;
            }
        }
        return $result;
    }

    public function removeExtIdentity(User $user, $provider)
    {
        $user->setAttribute("cuniprincipalname;x-ext-" . strtolower($provider), []);
        $user->save();
    }

    public function setExtIdentity(User $user, $provider, $id)
    {
        $user->setAttribute("cuniprincipalname;x-ext-" . strtolower($provider), $id);
        $user->save();
    }
}

