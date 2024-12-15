<?php
namespace App\Interfaces;

interface UserManager
{
    public function findUserByIdentifier($id);
    
    public function findUserByData($data);
    
    public function getIdentity($user);
}

