<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceManager
{
        public function createExtSourceWithAttributes(array $data) : ExtSource;
        
        public function getConnector(ExtSource $ext_source) : ExtSourceConnector;
        
        public function getAuthenticator($name) : IdentityProvider;
        
        public function listAuthenticators();
        
        public function getUpdatableAttributes(ExtSource $ext_source);
}

