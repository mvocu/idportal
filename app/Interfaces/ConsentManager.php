<?php

namespace App\Interfaces;

interface ConsentManager
{
    public function isAllowed($object, $attr, $value) : bool;
    
}

