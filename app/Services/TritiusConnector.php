<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

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
            'debug' => false,
        ]);
    }
    
    public function listUsers(ExtSource $source)
    {
        $limit = 200;
        for($offset = 0, $total = 0, $users = null; 
            is_null($users) || $users->count() < $total; 
            $offset += $count) 
        {
            $response = $this->client->get('user', [ 'query' => [ 'offset' => $offset, 'limit' => $limit ] ]);
            $result = $this->parseResponse($response);
            $total = $result['count'];
            $count = count($result['results']);
            $users = is_null($users) ? collect($result['results']) : $users->concat($result['results']);
        }
        $valid_users = $users->filter(function($item, $key) { return !empty($item['username']) && !empty($item['note']); });
        return $valid_users->map(function($item, $key) { 
            return $this->makeResource($item, "username", "parent"); 
        });
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::findUser()
     */
    public function findUser(ExtSource $source, $data)
    {
        $result = $this->parseResponse($this->client->get('user/identifier/' . $data));
        return collect($result['results']);
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::getUser()
     */
    public function getUser(\App\Models\Database\ExtSource $source, $id)
    {
        return $this->makeResource($this->parseResponse($this->client->get('user/' . $id)), "username", "parent_username");     
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
                $result['results'][$key]['note'] = $note[0];
                if(count($note) > 1) { 
                    $result['results'][$key]['parent'] = json_encode(['email' => $note[1]]);
                }
            }
	    if(!empty($item['birthdate'])) {
	        $result['results'][$key]['birthdate'] = (new Carbon($item['birthdate']))->addRealHours(3)->startOfDay()->toString();
	    }
        }
        return $result;
    }
    
}

