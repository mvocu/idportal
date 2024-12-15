<?php

namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use App\Traits\MapsToIdentity;

class eduResource extends CasResource implements IdentityResource
{
    use MapsToIdentity;

    protected $_attributeMap = [
        "edu_principal_name" => IdentityResource::ATTR_NICKNAME,
        "edu_cn" => IdentityResource::ATTR_NAME,
        #"edu_targeted_id" => "edu_targeted_id",
        #"edu_unique_id" => "edu_unique_id",
        "edu_given_name" => IdentityResource::ATTR_GIVEN_NAME,
        "edu_email" => IdentityResource::ATTR_EMAIL,
        #"edu_home_organization" => "edu_home_organization",
        "edu_sn" => IdentityResource::ATTR_FAMILY_NAME,
        "edu_unstructured_name" => IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
        "edu_unique_id" => IdentityResource::ATTR_ADMINISTRATIVE_NUMBER,
        #"edu_personal_unique_code" => "edu_personal_unique_code",
    ];
    
}
