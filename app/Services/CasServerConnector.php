<?php
namespace App\Services;

use App\Interfaces\CasServer as CasServerInterface;
use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;

class CasServerConnector implements CasServerInterface
{
    const GAUTH_ENDPOINT = 'gauthCredentialRepository';
    const WEBAUTHN_ENDPOINT = 'webAuthnDevices';
    const DEVICES_ENDPOINT = 'multifactorTrustedDevices';
    
    protected $client;
    protected $username;
    protected $password;
    
    public function __construct(Repository $config)
    {
        $uri = $config->get('cas.connection.base_uri');
        if(!str_ends_with($uri, '/')) {
            $uri .= '/';
        }
        $this->client = new Client([
            'base_uri' => $uri
            
        ]);
        $this->username = $config->get('cas.connection.username');
        $this->password = $config->get('cas.connection.password');
    }
    
    public function request($method, $uri)
    {
        $response = $this->client->request($method, $uri, ['auth' => [$this->username, $this->password]]);
        if($response->getStatusCode() != 200) {
            return null;
        }
        return $response->getBody()->getContents();
    }
    
    public function getGauthCredentials($id) {
        return $this->collect($this->request('GET', self::GAUTH_ENDPOINT . '/' . $id));
    }
    
    public function getWebAuthnDevices($id) {
        return $this->collect($this->request('GET', self::WEBAUTHN_ENDPOINT . '/' . $id));
    }
    
    public function getTrustedDevices($id) {
        $this->request('GET', self::DEVICES_ENDPOINT . '/' . $id);
        return $this->collect($this->request('GET', self::DEVICES_ENDPOINT . '/' . $id));
    }
    
    protected function collect($json) {
        $data = json_decode($json);
        return collect($data);
    }
}

