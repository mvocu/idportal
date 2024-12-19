<?php
namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use App\Traits\MapsToIdentity;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Interfaces\IdentityManager;

class FormResource extends JsonResource implements IdentityResource
{
    use MapsToIdentity;
    
    const FORM_ATTRIBUTES = [
        'given_name' => IdentityResource::ATTR_GIVEN_NAME, 
        'family_name' => IdentityResource::ATTR_FAMILY_NAME, 
        'birthdate' => IdentityResource::ATTR_BIRTHDATE, 
        'administrative_number' => IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
        'phone_number' => IdentityResource::ATTR_PHONE_NUMBER, 
        'email' => IdentityResource::ATTR_EMAIL,
        'address' => IdentityResource::ATTR_ADDRESS,
    ]; 
    
    const FORM_ADDRESS_ATTRIBUTES = [
        'street' => IdentityResource::ATTR_ADDRESS_STREET, 
        'street_number' => IdentityResource::ATTR_ADDRESS_STREET_NUMBER, 
        'evidence_number' => IdentityResource::ATTR_ADDRESS_EV_NUMBER, 
        'city' => IdentityResource::ATTR_ADDRESS_CITY, 
        'postal_code' => IdentityResource::ATTR_ADDRESS_POSTAL_CODE, 
        'country' => IdentityResource::ATTR_ADDRESS_COUNTRY,
    ];
    
    protected function getAttributeMap() {
        return self::FORM_ATTRIBUTES;        
    }
    
    public function translateAddress($value) {
        $data = [];
        foreach (self::FORM_ADDRESS_ATTRIBUTES as $src => $dest) {
            if(!empty($value[$src])) {
                $data[$dest] = $value[$src];
            }
        }
        return $data;
    }
    
    public function translatePhoneNumber($number) {
        $number = preg_replace("/\s+/", "", $number);
        if(empty($number)) {
            return $number;
        }
        if($number[0] == '+') {
            return $number;
        }
        if(strlen($number) == 9) {
            return "+420$number";
        }
        return "+$number";
    }
    
    public function computeLoa($data) {
        #echo "<pre>", var_dump($data), "</pre>";
        if(empty($data[IdentityResource::ATTR_EMAIL]) && empty($data[IdentityResource::ATTR_PHONE_NUMBER])) {
            return null;
        }
        return IdentityManager::LOA_NONE;
    }
    
}

