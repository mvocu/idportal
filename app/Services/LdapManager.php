<?php

namespace App\Services;


use Adldap\AdldapInterface;
use App\Models\Database\User;
use App\Models\Ldap\LdapUser;
use Illuminate\Support\Str;
use App\Interfaces\LdapManager as LdapManagerInterface;

class LdapManager implements LdapManagerInterface
{
    protected $ldap;
    
    public function __construct(AdldapInterface $ldap) {
        $this->ldap = $ldap;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapManager::createUser()
     */
    public function createUser(User $user): LdapUser
    {
        $data = $this->_mapUser($user);
        $data['dn'] = $this->buildDN($user);
        $data['uid'] = $this->_generateLogin($user);
        $ldapuser = $this->ldap->getProvider('admin')->make()->user($data);
        $ldapuser->save();
        return $ldapuser;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapManager::deleteUser()
     */
    public function deleteUser(User $user): bool
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapManager::renameUser()
     */
    public function renameUser(User $user): LdapUser
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapManager::updateUser()
     */
    public function updateUser(User $user): LdapUser
    {
        // TODO Auto-generated method stub
        
    }

    public function buildDN(User $user) {
        $baseDN = $this->ldap->getProvider('admin')->getConfiguration()->get('base_dn');
        return 'uniqueIdentifier='.$user->identifier.',ou=People,'.$baseDN;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapManager::changePassword()
     */
    public function changePassword(LdapUser $user, $password)
    {
       // use admin connection for the user
       $user->getQuery()->setConnection($this->ldap->getProvider('admin')->getConnection());
       $user->setPassword($password);
       $user->save();
    }

    protected function _mapUser(User $user) {
        $data = [
            'uniqueIdentifier' => $user->identifier,
            'givenName' => $user->first_name,
            'sn' => $user->last_name,
            'cn' => $user->first_name . ( empty($user->middle_name) ? '' : ' ' . $user->middle_name ) . ' ' . $user->last_name,
        ];
        if(!empty($user->country)) {
            $data['c'] = $user->country;
        }
        $phones = $user->phones;
        if($phones->isNotEmpty()) {
            $data['telephoneNumber'] = $phones->pluck('phone')->all();
        }
        $emails = $user->emails;
        if($emails->isNotEmpty()) {
            $data['mail'] = $emails->pluck('email')->all();
        }
        $address = $user->residency;
        if(!empty($address)) {
            $data['street'] = $address->street;
            $data['l'] = $address->city;
            $data['st'] = $address->state;
            $data['postalCode'] = $address->post_number;
            $data['houseIdentifier'] = $addres->getHouseNumber();
        }
        $addresses = $user->addresses;
        if($addresses->isNotEmpty()) {
            $data['postalAddress'] = $addresses->map(function($item, $key) { return $item->getFormattedAddress(); })
                                        ->all();
        }
        return $data;
    }
    
    protected function _generateLogin(User $user) {
        $last_name = Str::ascii($user->last_name);
        $first_name = Str::ascii($user->first_name);
        $max = strlen($first_name);
        for($i = 1; $i <= $max; $i++) {
            $login = strtolower(substr($first_name, 0, $i) . $last_name);
            $coll = $this->ldap->search()->where('uid', '=', $login)->get();
            if($coll->isEmpty()) {
                return $login;
            }
        }
        $max = strlen($last_name);
        for($i = 1; $i <= $max; $i++) {
            $login = strtolower($first_name . substr($last_name, 0, $i));
            $coll = $this->ldap->search()->where('uid', '=', $login)->get();
            if($coll->isEmpty()) {
                return $login;
            }
        }
        $max = 255;
        $base_login = strtolower(substr($first_name, 0, 1) . $last_name);
        for($i = 1; $i <= $max; $i++) {
            $login = $base_login . "$i";
            $coll = $this->ldap->search()->where('uid', '=', '$login')->get();
            if($coll->isEmpty()) {
                return $login;
            }
        }
        return $null;
    }
    
}

