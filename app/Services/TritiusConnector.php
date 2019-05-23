<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;

class TritiusConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{
    protected $config;
    protected $client;
    
    public function __construct($config) {
        $this->config = $config;    
        $this->client = new Client([
            'base_uri' => $this->config['url'] . (ends_with($this->config['url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'headers' => [ 
                'Content-type' => 'application/vnd.tritius-v1.0+json', 
                'X-Library' => 1, 
                'Accept' => 'application/vnd.tritius-v1.0+json'
            ],
            'debug' => true,
        ]);
    }
    
    public function listUsers(ExtSource $source)
    {
        $limit = 200;
        for($offset = 0, $total = 0, $users = null; 
            is_null($users) || $users->count() < $total; 
            $offset += $count) 
        {
            $response = $this->client->get('users', [ 'query' => [ 'offset' => $offset, 'limit' => $limit ] ]);
            $result = $this->parseResponse($response);
            $total = $result['count'];
            $count = count($result['results']);
            $users = is_null($users) ? collect($result['results']) : $users->concat($result['results']);
        }
        return $users->map(function($item, $key) { return $this->makeResource($item, "username", "note"); });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::findUser()
     */
    public function findUser(ExtSource $source, $data)
    {
        $result = $this->parseResponse($this->client->get('users/identifier/' . $data));
        return collect($result['results']);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::getUser()
     */
    public function getUser(\App\Models\Database\ExtSource $source, $id)
    {
        return $this->makeResource($this->parseResponse($this->client->get('users/' . $id)), "username", "note");     
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

