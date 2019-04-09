<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;

class PortalObcanaConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{
    protected $config;
    protected $client;
    
    public function __construct($config) {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['url'] . (ends_with($this->config['url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'debug' => true,
        ]);
    }
    
    public function listUsers(ExtSource $source)
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::findUser()
     */
    public function findUser(ExtSource $source, $data)
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::getUser()
     */
    public function getUser(\App\Models\Database\ExtSource $source, $id)
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::supportsUserListing()
     */
    public function supportsUserListing(\App\Models\Database\ExtSource $source)
    {
        return false;
    }
    
    protected function parseResponse($response) {
    }
    
    
}

