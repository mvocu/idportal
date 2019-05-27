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
        $valid_users = $users->filter(function($item, $key) { return !empty($item['username']) && !empty($item['note']); });
        $readercode_map = $valid_users->mapWithKeys(function($item) { return [ $item['readerNumber'] => $item['username']]; });
        return $valid_users->map(function($item, $key) use ($readercode_map) { 
            if(array_key_exists('parent_code', $item)) {
                $item['parent_username'] = $readercode_map->get($item['parent_code']);
            }
            return $this->makeResource($item, "username", "parent_username"); 
        });
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
        return $this->makeResource($this->parseResponse($this->client->get('users/' . $id)), "username", "parent_username");     
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::supportsUserListing()
     */
    public function supportsUserListing(\App\Models\Database\ExtSource $source)
    {
        return true;        
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
        $result = json_decode($json, true);
        foreach($result['results'] as $key => $item) {
            if(!empty($item['note'])) {
                $note = explode(';', $item['note']);
                $item['note'] = $note[0];
                if(count($note) > 1) { 
                    $item['parent_code'] = $note[1];
                }
            }
        }
        return $result;
    }
    
}

