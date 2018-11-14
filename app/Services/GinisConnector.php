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
        $soapClient = new \SoapClient($this->config['endpoints']['gin']."?wsdl", [ 'trace' => true ]);
        $securityHeader = WsSecurity::createWsSecuritySoapHeader($this->config['username'], $this->config['password'], false);
        $soapClient->__setSoapHeaders([ $securityHeader ]);
        $request = new \SoapVar('<requestXml xmlns="http://www.gordic.cz/xrg/gin/esu/najdi-esu/request/v_1.0.0.0"><Xrg ixsExt="" xmlns="http://www.gordic.cz/xrg/gin/esu/najdi-esu/request/v_1.0.0.0"><Najdi-esu><Typ-esu>fyz-osoba</Typ-esu></Najdi-esu></Xrg></requestXml>', XSD_ANYXML);
        try {
            $result = $soapClient->NajdiEsu(['requestXml' => $request ]);
        } catch(\SoapFault $e) {
            echo $e->getMessage();
        }
        echo $soapClient->__getLastRequest();
        echo $soapClient->__getLastRequestHeaders();
        return $result;
    }
}

