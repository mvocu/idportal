<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Interfaces\ExtUserResourceInterface;
use App\Models\Database\UserExt;

class ExtUserResource extends Resource implements ExtUserResourceInterface
{
    public function toArray($request) 
    {
        if(is_array($this->resource)) {
            return $this->resource['attributes'];
        } elseif ($this->resource instanceof UserExt) {
            $result = [];
            foreach($this->resource->attributes as $attribute) {
                $name = $attribute->attrDesc->name;
                if(array_key_exists($name, $result)) {
                    $old = $result[$name];
                    $result[$name] = [ $old, $attribute->value ]; 
                } else {
                    $result[$name] = $attribute->value;
                }
            }
            return $result;
        } else {
            return [];
        }
    }

    public function getId() 
    {
        if(is_array($this->resource)) {
            return $this->resource['id'];
        } elseif ($this->resource instanceof UserExt) {
            return $this->resource->login;
        } else {
            return null;
        }
    }
    
    public function getParent()
    {
        if(is_array($this->resource)) {
            return $this->resource['parent']; 
        } elseif ($this->resource instanceof UserExt) {
            $this->resource->parent_id;
        } else {
            return null;
        }
    }
    
    public function isActive() 
    {
        if(is_array($this->resource)) {
            return array_key_exists('active', $this->resource) ? $this->resource['active'] : true;    
        } elseif ($this->resource instanceof UserExt) {
            return $this->resource->active;
        } else {
            return false;
        }
    }
}

