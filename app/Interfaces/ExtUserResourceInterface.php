<?php

namespace App\Interfaces;

interface ExtUserResourceInterface
{
        public function getId();
        
        public function getParent();
        
        public function isActive();
}

