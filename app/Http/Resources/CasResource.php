<?php
namespace App\Http\Resources;

use App\Interfaces\IdentityResource;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class CasResource extends JsonResource implements IdentityResource
{

    protected static $_baseAttributeMap = [
        'auth_loa' => IdentityResource::LOA,
        'cunipersonalid' => IdentityResource::CUNIPERSONALID,
    ];
    
    protected function getAttributeMap()
    {
        return self::$_baseAttributeMap;
    }
    
}

