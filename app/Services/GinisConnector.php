<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use WsdlToPhp\WsSecurity\WsSecurity;

class GinisConnector implements ExtSourceConnector
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
    
    public function listUsers(ExtSource $source)
    {
        try {
            $result = $this->soapClient->NajdiEsu(
                [ "requestXml" => [ 
                    "Xrg" => [ 
                        "Rizeni-prehledu" => [ 
                            "Rozsah-prehledu" => "rozsireny" 
                        ], 
                        "Najdi-esu" => 
                        [ 
                            "Obec" => "Úv*"
                        ] 
                    ] 
                ] ]);
        } catch(\SoapFault $e) {
            return null;
        }
        return $result;
    }
}

