<?php

namespace App\Services;


use Adldap\AdldapInterface;
use App\Models\Database\User;
use App\Models\Ldap\LdapUser;
use Illuminate\Support\Str;
use App\Interfaces\LdapConnector as LdapConnectorInterface;
use App\Interfaces\ConsentManager;
use App\Events\LdapUserCreatedEvent;
use App\Events\LdapUserUpdatedEvent;
use App\Models\Database\ExtSource;

class LdapConnector implements LdapConnectorInterface
{
    protected $ldap;
    
    protected $consent_mgr;
    
    private $ext_sources;
    
    public function __construct(AdldapInterface $ldap, ConsentManager $cmgr) {
        $this->ldap = $ldap;
        $this->consent_mgr = $cmgr;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::findUser()
     */
    public function findUser(User $user)
    {
        $dn = $this->buildDN($user);
        $result = $this->ldap->search()->findByDn($dn);
        if($result != null && $result->exists) return $result;
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::createUser()
     */
    public function createUser(User $user): LdapUser
    {
        $data = $this->_mapUser($user);
        $data['dn'] = $this->buildDN($user);
        if(empty($data['uid'])) {
            $data['uid'] = $this->_generateLogin($user);
        }
        $ndata = array_filter($data, function($el) { return !empty($el); });
        $ldapuser = $this->ldap->getProvider('admin')->make()->user($ndata);
        $ldapuser->save();
        event(new LdapUserCreatedEvent($ldapuser->getDn()));
        return $ldapuser;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::deleteUser()
     */
    public function deleteUser(User $user): bool
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::renameUser()
     */
    public function renameUser(User $user): LdapUser
    {
        // TODO Auto-generated method stub
        
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::updateUser()
     */
    public function updateUser(User $user)
    {
        $this->ext_sources = ExtSource::all();
        $entry = $this->ldap->getProvider('admin')->search()->findByDn($this->buildDN($user));
        if($entry == null) return null;
        $data = $this->_mapUser($user);
        $entry->fill($data);
        $entry->save();
        event(new LdapUserUpdatedEvent($entry->getDn()));
        return $entry;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::syncUsers()
     */
    public function syncUsers(\Illuminate\Support\Collection $users)
    {
        $this->ext_sources = ExtSource::all();
        foreach($users as $user) {
            $entry = $this->findUser($user);
            if($entry == null) {
                $this->createUser($user);
            } else {
                $this->updateUser($user, $entry);
            }
        }
    }

    public function buildDN(User $user) {
        $baseDN = $this->ldap->getProvider('admin')->getConfiguration()->get('base_dn');
        return 'uniqueIdentifier='.$user->identifier.',ou=People,'.$baseDN;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::changePassword()
     */
    public function changePassword(LdapUser $user, $password)
   {
        // use admin connection for the user
        $user->getQuery()->setConnection($this->ldap->getProvider('admin')->getConnection());
        $user->setPassword($password);
        $user->save();
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\LdapConnector::findUserByExtSource()
     */
    public function findUserByExtSource(ExtSource $source, $id)
    {
        $name = Str::kebab(Str::lower(Str::ascii($source->name)));
        $query = $this->ldap->search()->rawFilter('(employeenumber;x-'.$name.'='.$id.')');
        $result = $query->get()->first();
        return $result;
    }

    protected function _mapUser(User $user) {
        if(empty($user->last_name)) {
            $data = [
                'uniqueIdentifier' => $user->identifier,
                'sn' => $user->identifier,
                'cn' => $user->identifier,
                'uid' => $user->identifier,
            ];
        } else {
            $data = [
                'uniqueIdentifier' => $user->identifier,
                'sn' => $user->last_name,
                'cn' => $user->first_name . ( empty($user->middle_name) ? '' : ' ' . $user->middle_name ) . ' ' . $user->last_name,
            ];
        }
        if(!empty($user->first_name)) {
            $data['givenName'] = $user->first_name;
        } else {
            $data['givenName'] = null;
        }
        if(!empty($user->country)) {
            $data['c'] = $user->country;
        } else {
            $data['c'] = null;
        }
        
        $phones = $user->phones;
        if($phones->isNotEmpty()) {
            $data['telephoneNumber'] = $phones->pluck('phone')->unique()->all();
        } else {
            $data['telephoneNumber'] = array();
        }
        $emails = $user->emails;
        if($emails->isNotEmpty()) {
            $data['mail'] = $emails->pluck('email')->unique()->all();
        } else {
            $data['mail'] = array();
        }
        $address = $user->residency;
        if(!empty($address)) {
            $data['street'] = $address->street;
            $data['l'] = $address->city;
            if(!empty($address->state)) $data['st'] = $address->state;
            if(!empty($address->post_number)) $data['postalCode'] = $address->post_number;
            $data['houseIdentifier'] = $address->getHouseNumber();
        } else {
            $data['street'] = null;
            $data['l'] = null;
            $data['st'] = null;
            $data['postalCode'] = null;
            $data['houseIdentifier'] = null;
        }
        $addresses = $user->addresses;
        if($addresses->isNotEmpty()) {
            $data['postalAddress'] = $addresses->map(function($item, $key) { return $item->getFormattedAddress(); })
                                        ->unique()->all();
        } else {
            $data['postalAddress'] = array();
        }

        $accounts = $user->accounts;
        if($accounts->isNotEmpty()) {
            foreach($accounts as $account) {
                $login = $account->login;
                if(!empty($login)) {
                    $name = Str::kebab(Str::lower(Str::ascii($account->extSource->name)));
                    $data['employeeNumber;x-'.$name] = $login;
                } else {
                    $data['employeeNumber;x-'.$name] = null;
                }
            }
        }
        if(!empty($this->ext_sources)) {
            foreach($this->ext_sources as $source) {
                $name = Str::kebab(Str::lower(Str::ascii($source->name)));
                if(!array_key_exists('employeeNumber;x-'.$name, $data)) {
                    $data['employeeNumber;x-'.$name] = null;
                }
            }
        }
        
        $parent = $user->parent;
        if(!empty($parent)) {
            $data['manager'] = $this->buildDN($parent);    
        } else {
            $data['manager'] = null;
        }
        
        return $data;
    }
    
    protected function _generateLogin(User $user) {
        $last_name = Str::ascii(trim($user->last_name));
        $first_name = Str::ascii(trim($user->first_name));
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

