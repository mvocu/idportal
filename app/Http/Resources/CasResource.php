<?php
namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class CasResource extends JsonResource implements IdentityResource
{

    protected static $_baseAttributeMap = [
        'auth_loa' => IdentityResource::LOA,
        // if, for whatever reason, we need to convert local CAS user into resource
        'cuni_personalid' => IdentityResource::CUNIPERSONALID,
        #'cunipersonalid' => IdentityResource::CUNIPERSONALID,
    ];
    
    protected function getAttributeMap()
    {
        return self::$_baseAttributeMap;
    }
    
    public function computeLoa($data) {
        // Take into account all identity attributes and LOA presented by external source:
        //   - there is no trust without provided name (both given and family) and birthdate
        if(empty($data[IdentityResource::ATTR_FAMILY_NAME]) 
            || empty($data[IdentityResource::ATTR_GIVEN_NAME])
            || empty($data[IdentityResource::ATTR_BIRTHDATE])) {
            return null;                
        }
        return isset($data[IdentityResource::LOA]) ? $data[IdentityResource::LOA] : null;        
    }
}

