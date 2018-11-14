<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceConnector
{
    public function listUsers(ExtSource $source);
    
}

