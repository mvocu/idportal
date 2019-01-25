<?php
namespace Services;

use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;

class ADConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{

    public function findUser(ExtSource $source, $user)
    {}

    public function getUser(ExtSource $source, $id)
    {}

}

