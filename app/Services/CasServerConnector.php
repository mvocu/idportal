<?php
namespace App\Services;

use App\Interfaces\CasServerInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;

class CasServerConnector implements CasServerInterface
{
    protected Client $client;
    
    public function __construct(Repository $config)
    {
        $this->client = new Client([
            'base_uri' => $config->get('cas.connection.base_uri')
            
        ]);
    }
    
    public function request(... $args)
    {
        return $this->client->request(... $args);
    }
}

