<?php
namespace App\Interfaces;

use App\Http\Resources\ExtUserResource;
use App\Models\Database\UserExt;

interface RegistrationManager
{
        public function getExtUserResource($data);
        
        public function createUserExt(ExtUserResource $resource);
        
        public function activateUser(UserExt $user);
        
}

