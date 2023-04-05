<?php
namespace App\Models\Cas;

use stdClass;
use Illuminate\Support\Facades\Date;

/*
 * JSON string:
 * 
 * [{
 *  "@class":"org.apereo.cas.gauth.credential.GoogleAuthenticatorAccount",
 *  "scratchCodes":[1,2,3,4,5],
 *  "id":1668007719867,
 *  "secretKey":"XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
 *  "validationCode":123456,
 *  "username":"12345678",
 *  "name":"mobil",
 *  "registrationDate":"2022-11-09T15:28:39.86727Z"
 *  }]
 */
class GauthRecord extends Model
{
    protected $id;
    
    protected $scratchCodes;
    
    protected $secretKey;
    
    protected $username;
    
    protected $name;
    
    protected $registrationDate;
    
    public static function forUser($id) {
        if(!self::$booted) {
            self::boot();
        }

        $objects = self::$cas->getGauthCredentials($id);
        return $objects->map(function($item, $key) { return self::from($item); });
    }
    
    public function __construct() {
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getOwner() {
        return $this->username;
    }
    
    public function getRegistrationDate() {
        return Date::createFromTimeString($this->registrationDate);
    }
    
    public function getScratchCodes() {
        return $this->scratchCodes;
    }
}

