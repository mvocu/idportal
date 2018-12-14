<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Collection;

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
        $response = $this->client->get('users');
        $result = $this->parseResponse($response);
        return collect($result['results']);
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
        return $this->parseResponse($this->client->get('users/' . $id));     
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

