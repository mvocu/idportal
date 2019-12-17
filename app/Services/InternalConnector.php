<?php
namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;
use App\Http\Resources\ExtUserResource;

class InternalConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{

    public function findUser(ExtSource $source, $user)
    {
        return null;   
    }

    public function modifyUser(ExtUserResource $user_ext, $data)
    {
        return $user_ext;
    }

    public function listUsers(ExtSource $source)
    {
        return null;
    }

    public function getUser(ExtSource $source, $id)
    {
        return null;
    }

    public function supportsUserListing(ExtSource $source)
    {
        return false;
    }

    public function validateUpdate(ExtSource $source, $data, $validator)
    {
        return true;
    }

}

