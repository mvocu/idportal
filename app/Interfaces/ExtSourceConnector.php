<?php

namespace App\Interfaces;

use App\Http\Resources\ExtUserResource;
use App\Models\Database\ExtSource;

interface ExtSourceConnector
{
    public function findUser(ExtSource $source, $user);
    
    public function getUser(ExtSource $source, $id);
    
    public function listUsers(ExtSource $source);
    
    public function supportsUserListing(ExtSource $source);
    
    public function validateUpdate(ExtSource $source, $data, &$validator);
    
    public function modifyUser(ExtUserResource $user_ext, $data);
}

