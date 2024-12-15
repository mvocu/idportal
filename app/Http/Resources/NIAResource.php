<?php

namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use App\Traits\MapsToIdentity;

class NIAResource extends CasResource implements IdentityResource
{
    use MapsToIdentity;

    protected $_attributeMap = [
        // OIDC claim => IdentityResource attribute
        #'name' => IdentityResource::ATTR_NAME,
        'nia_given_name' => IdentityResource::ATTR_GIVEN_NAME,
        'nia_family_name' => IdentityResource::ATTR_FAMILY_NAME,
        'nia_birthdate' => IdentityResource::ATTR_BIRTHDATE,
        'gender' => IdentityResource::ATTR_GENDER,
        'nia_email' => IdentityResource::ATTR_EMAIL,
        'nia_email_verified' => IdentityResource::ATTR_EMAIL_VERIFIED,
        'nia_phone_number' => IdentityResource::ATTR_PHONE_NUMBER,
        'nia_phone_number_verified' => IdentityResource::ATTR_PHONE_NUMBER_VERIFIED,
        'nia_id_type' => IdentityResource::ATTR_DOCUMENT_TYPE,
        'nia_id_number' => IdentityResource::ATTR_DOCUMENT_NUMBER,
        'nia_address' => IdentityResource::ATTR_ADDRESS,
        #'nia_tr_address' => 'tr_address', 
    ];

    public function translateNiaAddress($value) {
        $vals = preg_split("/[\n]/", $value);
        $nums = explode("/", $vals[0]);
        return [
            IdentityResource::ATTR_ADDRESS_EV_NUMBER => $nums[0],
            IdentityResource::ATTR_ADDRESS_STREET_NUMBER => isset($nums[1]) ? $nums[1] : null,
            IdentityResource::ATTR_ADDRESS_STREET => $vals[1],
            IdentityResource::ATTR_ADDRESS_POSTAL_CODE => $vals[3],
            IdentityResource::ATTR_ADDRESS_CITY => $vals[2],
            IdentityResource::ATTR_ADDRESS_CITY_PART => $vals[4],
        ];
    }
    
    public function translateNiaTrAddress($value) {
        return htmlentities(base64_decode($value));
    }
    
}
