<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceConnector
{
    public function findUser(ExtSource $source, $user);
    
    public function getUser(ExtSource $source, $id);
    
    public function listUsers(ExtSource $source);
    
    public function supportsUserListing(ExtSource $source);
    
}

