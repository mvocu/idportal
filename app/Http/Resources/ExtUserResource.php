<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExtUserResource extends JsonResource
{
    public function toArray($request) 
    {
        return $this->resource['attributes'];
    }

    public function getId() 
    {
        return $this->resource['id'];
    }
    
    public function isActive() 
    {
        return array_key_exists('active', $this->resource) ? $this->resource['active'] : true;    
    }
}

