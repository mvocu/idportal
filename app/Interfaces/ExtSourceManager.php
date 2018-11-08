<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface ExtSourceManager
{
        public function createExtSourceWithAttributes(array $data) : ExtSource;
}

