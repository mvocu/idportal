<?php

namespace App\Models\Database;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class Contact extends Model
{
    public const TYPE_ADDRESS = 1;
    public const TYPE_EMAIL   = 2;
    public const TYPE_PHONE   = 3;
    public const TYPE_URI     = 4;
    public const TYPE_DATABOX = 5;
    public const TYPE_BANK    = 6;
    
    // maps contact type to relation accessor in User model
    public static $contactTypes = [ 
        self::TYPE_ADDRESS => 'addresses',
        self::TYPE_EMAIL   => 'emails',
        self::TYPE_PHONE   => 'phones',
        self::TYPE_URI     => 'uris',
        self::TYPE_DATABOX => 'dataBox',
        self::TYPE_BANK    => 'bankAccount',
        
    ];
    
    public static $contactModels = [
        'phones' => Phone::class,
        'emails' => Email::class,
        'bankAccounts' => BankAccount::class,
        'dataBox' => Databox::class,
        'uris' => Uri::class,
        'addresses' => Address::class
    ];
    
    
    protected $table = 'contact';
    
    protected $fillable = ['state', 'city', 'street', 'org_number', 'ev_number', 'post_number', 'email', 'phone', 
        'uri', 'databox', 'bank_account'];
    
    public function user() {
        return $this->belongsTo('App\Models\Database\User', 'user_id');
    }

    public function createdBy() {
        return $this->hasOne('App\Models\Database\UserExt', 'created_by');
    }
    
    public function modifiedBy() {
        return $this->hasOne('App\Models\Database\UserExt', 'modified_by');
    }

    public function setPhoneAttribute($value) {
        $value = preg_replace("/\s+/", "", $value);
        $length = strlen($value);
        if($length == 9) {
            $value = "+420" . $value;
        } else {
            if($value[0] != '+') 
                $value = "+" . $value;
        }
        if(strlen($value) > 15) {
            throw new Exception("Value " . $value . " is too long for phone.");
        }
        $this->attributes['phone'] = $value;
    }

    public function setStreetAttribute($value) {
        $this->attributes['street'] = Str::title($value);
    }

    public function setCityAttribute($value) {
        $this->attributes['city'] = Str::title($value);
    }
    
    public function setOrgNumberAttribute($value) {
        
        if(preg_match("/(\d+)/", $value, $matches)) {
            $this->attributes['org_number'] = $matches[1];
        } else {
            unset($this->attributes['org_number']);
        }
    }
    
    public function setPostNumberAttribute($value) {
        $this->attributes['post_number'] = preg_replace('/\s+/', '', $value);
    }
    
}
