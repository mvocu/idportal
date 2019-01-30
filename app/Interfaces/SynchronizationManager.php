<?php

namespace App\Interfaces;

use App\Models\Database\ExtSource;

interface SynchronizationManager
{

    public function synchronizeExtSource(ExtSource $es);
    
}

