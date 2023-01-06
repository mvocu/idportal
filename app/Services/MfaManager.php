<?php
namespace App\Services;

use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Models\Ldap\User;
use App\Models\Cas\GauthRecord;
use App\Models\Cas\MfaPolicy;
use App\Models\Cas\WebAuthnDevice;

class MfaManager implements MfaManagerInterface
{

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getGauthCredentials()
     */
    public function getGauthCredentials(User $user)
    {
        $gauth = $this->parse(json_decode($user->getFirstAttribute('casgauthrecord')));
        return collect($gauth)->map(function($item, $key) {
            return GauthRecord::from(($item));
        });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getWebAuthnDevices()
     */
    public function getWebAuthnDevices(User $user)
    {
        $data = $user->getFirstAttribute('caswebauthnrecord');
        if(empty($data)) {
            return [];
        }
        $data = [];
        return collect($data)->map(function($item, $key) {
            return WebAuthnDevice::from($item);
        });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getPolicy()
     */
    public function getPolicy(User $user)
    {
        return new MfaPolicy($user->mfaPolicy);
    }

    protected function parse($source)
    {
        if(is_array($source) && is_string($source[0]) && $source[0] == "java.util.ArrayList") {
            $result = [];
            foreach($source[1] as $value) {
                $result[] = $this->parse($value);
            }
            return $result;
        }
        if($source instanceof \stdClass) {
            $result = [];
            foreach(get_object_vars($source) as $key => $value) {
                $result[$key] = $this->parse($value);
            }
            return $result;
        }
        return $source;
    }
}

