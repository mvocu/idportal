<?php

namespace App\Services;

use App\Interfaces\ExtSourceConnector;
use App\Models\Database\ExtSource;
use Illuminate\Support\Facades\Config;
use WsdlToPhp\WsSecurity\WsSecurity;

class GinisConnector implements ExtSourceConnector
{
    protected $config;
    
    public function __construct() 
    {
        $this->config = Config::get('ginis');
    }
    
    public function listUsers(ExtSource $source)
    {
        $soapClient = new \SoapClient($this->config['endpoints']['gin']);
        $securityHeader = WsSecurity::createWsSecuritySoapHeader($this->config['username'], $this->config['password'], false);
        $soapClient->__setSoapHeaders([ $securityHeader ]);
        try {
            $result = $soapClient->NajdiEsu(
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

