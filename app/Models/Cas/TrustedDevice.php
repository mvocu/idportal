<?php
namespace App\Models\Cas;

use Illuminate\Support\Facades\Date;
use stdClass;

class TrustedDevice extends Model
{
    protected $id;
    
    protected $principal;
    
    protected $deviceFingerprint;
    
    protected $recordDate;
    
    protected $recordKey;
    
    protected $name;
    
    protected $expirationDate;
    
    public static function forUser($id) {
        if(!self::$booted) {
            self::boot();
        }
        
        $objects = self::$cas->getTrustedDevices($id);
        return $objects->map(function($item, $key) { return self::from($item); });
    }
    
    public function __construct() {
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPrincipal()
    {
        return $this->principal;
    }

    /**
     * @return mixed
     */
    public function getDeviceFingerprint()
    {
        return $this->deviceFingerprint;
    }

    /**
     * @return mixed
     */
    public function getRecordDate()
    {
        return $this->recordDate;
    }

    /**
     * @return mixed
     */
    public function getRecordKey()
    {
        return $this->recordKey;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return Date::createFromTimeString($this->expirationDate);
    }

    
}

