<?php

namespace App\Services;

use Exception;
use App\Interfaces\ExtSourceConnector;
use App\Services\AbstractExtSourceConnector;
use App\Models\Database\ExtSource;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ExtUserResource;

class PortalObcanaConnector extends AbstractExtSourceConnector implements ExtSourceConnector
{
    protected $config;
    protected $client;
    protected $update_client;
    
    public function __construct($config) {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['url'] . (ends_with($this->config['url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'debug' => false,
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
            unset($item['uuid']);
            return $this->makeResource($item, $this->config['id_name']); 
        });
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Services\AbstractExtSourceConnector::modifyUser()
     */
    public function modifyUser(ExtUserResource $user_ext, $data)
    {
        $this->update_client = new Client([
            'base_uri' => $this->config['update_url'] . (ends_with($this->config['update_url'], '/') ? '' : '/'),
            'auth' => [ $this->config['username'], $this->config['password'] ],
            'debug' => false,
        ]);
        $old = $user_ext->toArray(null);
        /*
         *  commented out filling in missing data
         *  
        if((!array_key_exists('email', $data) || empty($data['email'])) &&
            array_key_exists('email', $old)) {
            $data['email'] = $old['email'];
        }
        if((!array_key_exists('phone_number', $data) || empty($data['phone_number'])) && 
            array_key_exists('phone_number', $old)) {
            $data['phone_number'] = $old['phone_number'];
        }
        */
        $id = $old['identifier'];
        $response = $this->update_client->put($id, [ 'json' => $data ]);
        $result = $this->parseResponse($response);
        if(is_null($result)) {
            throw new Exception($this->lastStatus);
        }
        $result['identifier'] = $id;
        //$result['uuid'] = $id;
        return $this->makeResource($result, $this->config['id_name']);
    }

    /**
     * {@inheritDoc}
     * @see \App\Services\AbstractExtSourceConnector::validateUpdate()
     */
    public function validateUpdate(ExtSource $source, $data, &$validator)
    {
        $validator = Validator::make($data, [
                'email' => 'sometimes|required|email',
                'phone_number' => 'sometimes|required|phone'
        ]);
        return $validator->passes();
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
        return json_decode($json, true);
    }
    
    
}

