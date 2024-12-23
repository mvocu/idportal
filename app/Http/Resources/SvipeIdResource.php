<?php

namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use App\Traits\MapsToIdentity;

class SvipeIdResource extends CasResource implements IdentityResource
{
    use MapsToIdentity;
    
    protected $_attributeMap = [
        // OIDC claim => IdentityResource attribute
        'name' => IdentityResource::ATTR_NAME,
        'given_name' => IdentityResource::ATTR_GIVEN_NAME,
        'family_name' => IdentityResource::ATTR_FAMILY_NAME,
        'birthdate' => IdentityResource::ATTR_BIRTHDATE,
        'gender' => IdentityResource::ATTR_GENDER,
        'com.svipe:document_nationality' => IdentityResource::ATTR_NATIONALITY,
        'email' => IdentityResource::ATTR_EMAIL,
        'email_verified' => IdentityResource::ATTR_EMAIL_VERIFIED,
        'phone_number' => IdentityResource::ATTR_PHONE_NUMBER,
        'phone_number_verified' => IdentityResource::ATTR_PHONE_NUMBER_VERIFIED,
        'com.svipe:document_type_sdn' => IdentityResource::ATTR_DOCUMENT_TYPE,
        'com.svipe:document_number' => IdentityResource::ATTR_DOCUMENT_NUMBER,
        'com.svipe:document_administrative_number' => IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
    ];
    
    public function transformGender($value) {
        if(strlen($value) > 1) {
            return $value[0];
        }
        return $value;
    }
}
