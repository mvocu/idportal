<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceConnector
{
    public function findUser(ExtSource $source, $user);
    
    public function getUser(ExtSource $source, $id);
}

