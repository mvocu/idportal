<?php
namespace App\Services;

use App\Interfaces\MfaManager as MfaManagerInterface;
use App\Models\Ldap\User;
use App\Models\Cas\GauthRecord;
use App\Models\Cas\MfaPolicy;
use App\Models\Cas\WebAuthnDevice;
use App\Util\Base64Url;
use App\Models\Cas\TrustedDevice;

class MfaManager implements MfaManagerInterface
{

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getGauthCredentials()
     */
    public function getGauthCredentials(User $user)
    {
        $gauth = $this->parseJavaSerialization(json_decode($user->getFirstAttribute('casgauthrecord')));
        return collect($gauth)->map(function($item, $key) {
            return GauthRecord::from(($item));
        });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::deleteGauthCredentials()
     */
    public function deleteGauthCredentials(User $user, $id = null)
    {
        $user->deleteAttribute('casgauthrecord');
        $user->save();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getWebAuthnDevices()
     */
    public function getWebAuthnDevices(User $user)
    {
        $result = collect([]);
        $values = $user->getAttribute('caswebauthnrecord');
        if(empty($values)) {
            return [];
        }
        foreach($values as $record) {
            $data = $this->decodeJWERecord($record);
            $data = json_decode($data);
            if(empty($data)) {
                next;
            }
            collect($data)->map(function($item, $key) use ($result) {
                $result->add(WebAuthnDevice::from($item));
            });
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::deleteWebAuthnDevices()
     */
    public function deleteWebAuthnDevices(User $user, $id = null)
    {
        $user->deleteAttribute('caswebauthnrecord');
        $user->save();
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getPagerNumber()
     */
    public function getPagerNumber(User $user)
    {
        $pager = $user->getFirstAttribute('pager');
        if(empty($pager)) {
            $pager = $user->getFirstAttribute('pager;x-cuni-is');
        }
        return $pager;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getTrustedDevices()
     */
    public function getTrustedDevices(User $user)
    {
        return TrustedDevice::forUser($user->getFirstAttribute('cunipersonalid'));
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::deleteTrustedDevices()
     */
    public function deleteTrustedDevices(User $user, $id = null)
    {
        $devices = TrustedDevice::forUser($user->getFirstAttribute('cunipersonalid'));
        foreach($devices as $device) {
            if(empty($id) || $device->getId() == $id) {
                if(!$device->delete()) {
                    throw new \Exception("Failed to remove trusted device.");
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\MfaManager::getPolicy()
     */
    public function getPolicy(User $user)
    {
        return new MfaPolicy($user->getFirstAttribute('cuniMfaPolicy'));
    }

    public function setPolicy(User $user, MfaPolicy $policy)
    {
        $user->cuniMfaPolicy = $policy->getValue();
        $user->save();
    }
    
    protected function parseJavaSerialization($source)
    {
        if(is_array($source) && is_string($source[0]) && $source[0] == "java.util.ArrayList") {
            $result = [];
            foreach($source[1] as $value) {
                $result[] = $this->parseJavaSerialization($value);
            }
            return $result;
        }
        if($source instanceof \stdClass) {
            $result = [];
            foreach(get_object_vars($source) as $key => $value) {
                $result[$key] = $this->parseJavaSerialization($value);
            }
            return $result;
        }
        return $source;
    }


    /* 
     * This is gutted out version of JWE decryption from JOSE library bySpomky-Labs:
     *   - all algorithms are hardwired
     *   - no hash checking 
     *   - no header control/autodetection 
     */
    protected function decodeJWERecord($source) {
        $jwt_parts = explode('.', $source);
        // [0] header, [1] payload, [2] hash - all Base64Url encoded
        $jwe_parts = explode('.', Base64Url::decode($jwt_parts[1]));
        // [0] header, [1] enc_key, [2] iv, [3] cipher, [4] tag - all Base64Url encoded
        $cipher = Base64Url::decode($jwe_parts[3]);
        $iv = Base64Url::decode($jwe_parts[2]);
        // encryption key from configuration
        $cek = Base64Url::decode(env('CAS_WEBAUTHN_ENC_KEY', ""));
        // encryption key is the right half of the content encryption key (left half is HMAC)
        $key = mb_substr($cek, mb_strlen($cek, '8bit') / 2, null, '8bit');
        // AES128CBC-HMAC-SHA256 is the encryption
        $clear = openssl_decrypt($cipher, 'aes-128-cbc', $key, OPENSSL_RAW_DATA, $iv);
        // decompress
        return gzinflate($clear);
    }
}

