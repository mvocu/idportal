<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use App\Interfaces\ExtUserResourceInterface;
use App\Models\Database\UserExt;
use App\Models\Database\ExtSource;

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
            return array_key_exists('parent', $this->resource) ? $this->resource['parent'] : null; 
        } elseif ($this->resource instanceof UserExt) {
            $this->resource->parent;
        } else {
            return null;
        }
    }
    
    public function getTrustLevel(ExtSource $source) 
    {
        if(is_array($this->resource)) {
            if(array_key_exists('trust_level', $this->resource) && 
                !is_null($this->resource['trust_level']) &&
                $this->resource['trust_level'] <= $source->trust_level) {
                return $this->resource['trust_level'];
            } else {
                return $source->trust_level;
            }
        } elseif ($this->resource instanceof UserExt) {
            $this->resource->trust_level;
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

