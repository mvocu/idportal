<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;

use GuzzleHttp\Client;

class HeliosConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{

    protected $config;
    protected $client;
    
    public function __construct($config) {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['url'] . (ends_with($this->config['url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'headers' => [  'Accept' => 'application/json' ],
            'debug' => true,
        ]);
    }

    public function findUser(ExtSource $source, $user)
    {
        $result = $this->parseResponse($this->client->get('ucty/detail/o' . $user));
        return collect($result);
    }

    public function listUsers(ExtSource $source)
    {
        $response = $this->client->get('ucty/vse');
        $result = $this->parseResponse($response);
        return collect($result)->map(function($item, $key) { return $this->makeResource($item, "id_kos_is"); });
    }

    public function getUser(ExtSource $source, $id)
    {
        return $this->makeResource($this->parseResponse($this->client->get('ucty/detail/o' . $id)), "id_kos_is");
    }

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

