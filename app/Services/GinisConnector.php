<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use WsdlToPhp\WsSecurity\WsSecurity;
use App\Http\Resources\UserResource;
use Illuminate\Database\Eloquent\Collection;

class GinisConnector extends AbstractExtSourceConnector implements ExtSourceConnector 
{
    protected $config;
    protected $soapClient;
    
    public function __construct($config) 
    {
        $this->config = $config;
        $wsdl = resource_path() . "/wsdl/" . $this->config['endpoints']['gin'];
        $this->soapClient = new \SoapClient($wsdl);
        $securityHeader = WsSecurity::createWsSecuritySoapHeader($this->config['username'], $this->config['password'], false);
        $this->soapClient->__setSoapHeaders([ $securityHeader ]);
    }
    
    public function findUser(ExtSource $source, $user)
    {
        if($user instanceof UserResource) {
            $data = $this->getExtUserResource($source, $user)->toArray(null);
        } elseif (is_array($user)) {
            $data = $user;
        } else {
            $data = $user->toArray();
        }
        try {
            $result = $this->soapClient->NajdiEsu(
                [ "requestXml" => [ 
                    "Xrg" => [ 
                        "Rizeni-prehledu" => [ 
                            "Rozsah-prehledu" => "rozsireny" 
                        ], 
                        "Najdi-esu" => $data, 
                    ] 
                ] ]);
        } catch(\SoapFault $e) {
            $this->lastStatus = $e->getMessage();
            return new Collection();
        }
        $this->lastStatus = null;
        $gusers = [];
        foreach($result->{'Najdi-esuResult'}->Xrg->{'Najdi-esu'} as $guser) {
            $gusers[] = $this->mapResult($guser);
        }
        return collect($gusers);
    }
    
    public function getUser(ExtSource $source, $id) 
    {
        try {
            $result = $this->soapClient->DetailEsu(
                [ "requestXml" => [
                    "Xrg" => [
                        "Rizeni-prehledu" => [
                            "Rozsah-prehledu" => "rozsireny"
                        ],
                        "Detail-esu" => [ 'Id-esu' => $id ],
                    ]
                ] ]);
        } catch(\SoapFault $e) {
            $this->lastStatus = $e->getMessage();
            return null;
        }
        $this->lastStatus = null;
        return $this->mapResult($result->{'Detail-esuResult'}->Xrg->{'Detail-esu'});
    }
    
    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::listUsers()
     */
    public function listUsers(\App\Models\Database\ExtSource $source)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     * @see \App\Interfaces\ExtSourceConnector::supportsUserListing()
     */
    public function supportsUserListing(\App\Models\Database\ExtSource $source)
    {
        return false;        
    }

    protected function mapResult($data) {
        $result = [];
        foreach($data as $key => $value) {
            if(empty($value)) continue;
            if(is_object($value)) {
                $result[$key] = $value->{'_'};
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    
}

