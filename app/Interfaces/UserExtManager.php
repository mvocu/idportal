<?php

namespace App\Interfaces;

use App\Http\Resources\ExtUserResource;
use App\Http\Resources\UserResource;
use App\Models\Database\ExtSource;
use App\Models\Database\UserExt;
use Illuminate\Support\Collection;

interface UserExtManager
{
    public function getUserResource(UserExt $user_ext) : UserResource;
    
    public function getExtUserResource(UserExt $user_ext) : ExtUserResource;
  
    public function createUserWithAttributes(ExtSource $source, ExtUserResource $data) : UserExt;
    
    public function updateUserWithAttributes(ExtSource $source, UserExt $user, ExtUserResource $data) : UserExt;
    
    public function syncUsers(ExtSource $source, Collection $users, bool $complete = false);
    
    public function getUser(ExtSource $source, ExtUserResource $data);
    
    public function activateUser(UserExt $user_ext);
    
    public function activateUserByData(ExtSource $source, ExtUserResource $data);
    
    public function mapUserAttributes(ExtSource $source, $data);
    
    public function removeUser(ExtSource $source, UserExt $user);
}

