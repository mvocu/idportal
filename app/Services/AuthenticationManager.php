<?php
namespace App\Services;

use App\Interfaces\AuthenticationManager as UserExtManagerInterface;
use App\Auth\OidcUser;
use App\Models\Ldap\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use App\Interfaces\IdentityResource;

class AuthenticationManager implements UserExtManagerInterface
{
    const FEDERATION_CLIENTS = ['eduid', 'edugain'];

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\AuthenticationManager::findUserByExtIdentity()
     */
    public function findUserByExtIdentity($id)
    {
        return User::findBy('cuniprincipalname', $id);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\AuthenticationManager::listExternalIdentities()
     */
    public function listExternalIdentities(User $user)
    {
        $attrs = $user->getAttributes();
        $result = [];
        foreach($attrs as $name => $value) {
            if(strpos($name, "cuniprincipalname;x-ext-") !== false) {
                $provider = substr($name, strlen("cuniprincipalname;x-ext-"));
                $val = is_array($value) ? $value[0] : $value;
                if(strstr($provider, "-")) {
                    $parts = explode("-", $provider);
                    $client = $parts[0];
                    $provider = $parts[1];
                    $result[$client][$provider] = $val;
                } else {
                    $client = $provider;
                    $provider = null;
                    $result[$client] = $val;
                }
            }
        }
        return $result;
    }

    public function removeExtIdentity(User $user, $client, $provider = null)
    {
        $key = $this->_buildExtPrincipalKey($client, $provider);
        $user->setAttribute("cuniprincipalname;" . $key, []);
        $user->save();
    }

    public function setExtIdentity(User $user, $client, $provider, $id)
    {
        $key = $this->_buildExtPrincipalKey($client, $provider);
        $user->setAttribute("cuniprincipalname;" . $key, $id);
        $user->save();
    }
    
    public function getIdentity(OidcUser $user)
    {
        $client = $user->getRemoteClient();
        if(empty($client)) {
            return [];
        }
        $identityResource = App::makeWith('ext.idp.' . $client, [ 'resource' => $user ]);
        return $identityResource->toArray(null);
    }

    public function getRemoteProvider(OidcUser $user)
    {
        $client = $user->getRemoteClient();
        if(in_array($client, self::FEDERATION_CLIENTS)) {
            $identity = $this->getIdentity($user);
            if(array_key_exists(IdentityResource::ATTR_NICKNAME, $identity)) {
                $principal = $identity[IdentityResource::ATTR_NICKNAME];
            } else {
                $principal = $user->getAuthIdentifier();
            }
            if(strstr($principal, '@')) {
                $parts = explode('@', $principal);
            } else {
                $parts[0] = $principal;
                $saml2cred = $user->auth_saml2_credentials;
                if(!empty($saml2cred)) {
                    $parts[1] = $this->_getSaml2Issuer($saml2cred);
                } else {
                    $parts[1] = null;
                }
            }
            if(!empty($parts[1])) {
                return $parts[1];
            }
        }
        return $client;
    }
    
    public function isEligibleForRegistration($user) {
        $client = $user->getRemoteClient();
        if(!in_array($client, self::FEDERATION_CLIENTS)) {
            return true;
        }
        $identity = $this->getIdentity($user);
        if(array_key_exists(IdentityResource::ATTR_NICKNAME, $identity)) {
            return true;
        }
        $saml2cred = $user->auth_saml2_credentials;
        if(empty($saml2cred)) {
            return false;
        }
        $data = json_decode($saml2cred, true);
        if($data == null) {
            return false;
        }
        $nameid_format = $data["nameId"]["format"];
        switch($nameid_format) {
            case "urn:oasis:names:tc:SAML:2.0:nameid-format:transient":
                return false;
                
            default:
                return true;
        }
    }
 
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\AuthenticationManager::hasAuthentication()
     */
    public function hasAuthentication(User $user)
    {
        $pw = $user->getAttribute('userpassword');
        $exts = $this->listExternalIdentities($user);
        return !empty($pw) || !empty($exts);
    }

    protected function _buildExtPrincipalKey($client, $provider) 
    {
        $key = "x-ext-" . $client;
        if(!empty($provider)) {
            $key .= "-" . $provider;
        }
        return $key;
    }

    protected function _getSaml2Issuer($data) {
        $saml2cred = json_decode($data, true);
        if($saml2cred == null) {
            return null;
        }
        return $saml2cred["issuerId"];
    }
}

