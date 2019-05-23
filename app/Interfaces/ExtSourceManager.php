<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceManager
{
        public function createExtSourceWithAttributes(array $data) : ExtSource;
        
        public function getConnector(ExtSource $ext_source) : ExtSourceConnector;
        
        public function getUpdatableAttributes(ExtSource $ext_source);
}

