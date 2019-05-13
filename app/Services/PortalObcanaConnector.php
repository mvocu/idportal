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
        $response = $this->client->get('');
        $result = $this->parseResponse($response);
        $users =  collect($result);
        return $users->map(function($item, $key) { 
            unset($item['categories']); unset($item['zones']);
            $item['identifier'] = $item['uuid']; 
            return $this->makeResource($item, "uuid"); 
        });
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::findUser()
     */
    public function findUser(ExtSource $source, $data)
    {
            return null;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::getUser()
     */
    public function getUser(ExtSource $source, $id)
    {
        return null;
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::supportsUserListing()
     */
    public function supportsUserListing(ExtSource $source)
    {
        return false;
    }
    
    protected function parseResponse($response) {
        if($response->getStatusCode() != 200) {
            $this->lastStatus = $response->getStatusCode() . " " .$response->getReasonPhrase();
            return null;
        }
        $json = $response->getBody()->getContents();
        if(empty($json)) {
            $this->lastStatus = "Empty result.";
            return null;
        }
        return json_decode($json, true);
    }
    
    
}

