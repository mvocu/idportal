<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Interfaces\ExtUserResourceInterface;

class ExtUserResource extends JsonResource implements ExtUserResourceInterface
{
    public function toArray($request) 
    {
        return $this->resource['attributes'];
    }

    public function getId() 
    {
        return $this->resource['id'];
    }
    
    public function getParent()
    {
        return $this->resource['parent']; 
    }
    
    public function isActive() 
    {
        return array_key_exists('active', $this->resource) ? $this->resource['active'] : true;    
    }
}

