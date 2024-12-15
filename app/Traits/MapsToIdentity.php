<?php
namespace App\Traits;

use App\Auth\OidcUser;
use Illuminate\Support\Str;
use App\Interfaces\IdentityResource;
use App\Models\Ldap\User as LdapUser;

trait MapsToIdentity
{
    
    protected function getAttributeMap() 
    {
        $parent_map = parent::getAttributeMap();
        return array_merge($parent_map, $this->_attributeMap);
    }
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [];
        if($this->resource instanceof OidcUser) {
            $attrs = $this->resource->getAttributes();
            $data[IdentityResource::ATTR_EXTERNAL_ID] = $this->resource->getAuthIdentifier();
        } else if($this->resource instanceof LdapUser) {
            $attrs = $this->resource->toArray();
        } else {
            $attrs = $this->resource->toArray();
        }
        foreach($this->getAttributeMap() as $claim => $attr) {
            if(array_key_exists($claim, $attrs)) {
                $name = Str::camel("translate".$claim);
                if(method_exists($this, $name)) {
                    $value = call_user_func([$this, $name], $attrs[$claim]);
                } else {
                    $value = $attrs[$claim];
                }
                $data[$attr] = $value;
            }
        }
        return $data;
    }
    
}

