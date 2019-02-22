<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class ExtUserResource extends Resource
{
    public function toArray($request) {
        return $this->resource['attributes'];
    }

    public function getId() {
        return $this->resource['id'];
    }
    
}

