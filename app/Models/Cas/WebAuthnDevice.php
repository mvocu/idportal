<?php
namespace App\Models\Cas;

use Illuminate\Support\Facades\Date;
use stdClass;

/*
 *       {#3850
        +"userIdentity": {#3854
          +"name": "49237695",
          +"displayName": "49237695",
          +"id": "MUpY8AyGV0Ty1UmWSUTcmltNPR6ehHJ4FGjY8K-5d60",
        },
        +"credentialNickname": "mujYubiKey",
        +"registrationTime": "2023-01-05T15:24:49.684295Z",
        +"credential": {#3858
          +"credentialId": "XlVXzwcZK4qlz4js0TzOdgbkusEIrGEVaHeGfzMwrH8SW2-sw93gf6FfM9tRAx6LTvZsNAmxJ9haE0-Yh_Hhtw",
          +"userHandle": "MUpY8AyGV0Ty1UmWSUTcmltNPR6ehHJ4FGjY8K-5d60",
          +"publicKeyCose": "pQECAyYgASFYICWe0fwUFx2CTX0Ij88i9sQPv8oJYw2aOic99Fzd1wWEIlgggUPbkCNpPwbDiipeJ-s-i52zA-QOWcs1kQnOtJHUb_o",
          +"signatureCount": 39,
        },
        +"attestationMetadata": {#3856
          +"metadataIdentifier": "2fb54029-7613-4f1d-94f1-fb876c14a6fe",
          +"vendorProperties": {#3849
            +"url": "https://yubico.com",
            +"imageUrl": "https://developers.yubico.com/U2F/Images/yubico.png",
            +"name": "Yubico",
          },
          +"deviceProperties": {#3851
            +"deviceId": "1.3.6.1.4.1.41482.1.5",
            +"displayName": "YubiKey 4/YubiKey 4 Nano",
            +"deviceUrl": "https://support.yubico.com/support/solutions/articles/15000006486-yubikey-4",
            +"imageUrl": "https://developers.yubico.com/U2F/Images/YK4.png",
          },
        },
        +"username": "49237695",
      },
    ],
  }

 */
class WebAuthnDevice extends Model
{
 
    protected $userIdentity;
    
    protected $credentialNickname;
    
    protected $registrationTime;
    
    protected $credential;
    
    protected $attestationMetadata;
    
    protected $username;
    
    public static function from($source) {
        $instance = new static;
        if($source instanceof stdClass) {
            $instance->fill(get_object_vars($source));
        } else {
            $instance->fill($source);
        }
        return $instance;
    }
    
    public static function forUser($id) {
        if(!self::$booted) {
            self::boot();
        }
        
        $objects = self::$cas->getWebAuthnDevices($id);
        return $objects->map(function($item, $key) { return self::from($item); });
    }
    
    public function __construct() {
    }
    
    public function fill(array $attributes) {
        foreach(get_object_vars($this) as $name => $dummy) {
            $this->$name = $attributes[$name];
        }
    }
    
    public function getId() {
        return $this->credential->credentialId;
    }
    
    public function getName() {
        return $this->credentialNickname;
    }
    
    public function getOwner() {
        return $this->username;
    }
    
    public function getRegistrationDate() {
        //return Date::createFromTimeString($this->registrationTime);
        return  Date::createFromTimestamp($this->registrationTime);
    }
    
    public function getAttestationMetadata() {
        return $this->attestationMetadata;
    }
    
}

