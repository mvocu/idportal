<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;

class TritiusConnector implements ExtSourceConnector
{
    protected $config;
    protected $client;
    
    public function __construct($config) {
        $this->config = $config;    
        $this->client = new Client([
            'base_uri' => $this->config['url'] . (ends_with($this->config['url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'headers' => [ 'Content-type' => 'application/vnd.tritius-v1.0+json', 'X-Library' => 1 ],
            'debug' => true,
        ]);
    }
    
    public function listUsers(ExtSource $source)
    {
        return $this->client->get('users');
    }
}

